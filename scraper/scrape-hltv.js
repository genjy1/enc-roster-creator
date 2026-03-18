import { parse } from 'node-html-parser';
import { existsSync, mkdirSync, readFileSync, writeFileSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

// ─── Config ───────────────────────────────────────────────────────────────────

const BASE_URL         = 'https://www.hltv.org/players';
const OFFSET_STEP      = 52;
const PAGE_DELAY_MS    = 3000;
const FLARESOLVERR_URL = (process.env.FLARESOLVERR_URL ?? 'http://localhost:8191') + '/v1';
const OUTPUT_PATH      = process.env.OUTPUT_PATH
  ?? resolve(__dirname, '../backend/storage/app/players.json');
const PROGRESS_PATH    = resolve(dirname(OUTPUT_PATH), 'hltv_scrape_progress.json');

const LETTERS = [
  '#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
  'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
  'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
];

// ─── CLI args ─────────────────────────────────────────────────────────────────

const args   = process.argv.slice(2);
const getArg = (flag) => { const i = args.indexOf(flag); return i !== -1 ? args[i + 1] : null; };
const hasArg = (flag) => args.includes(flag);

const ARG_FROM  = getArg('--from')?.toUpperCase() ?? null;  // resume from letter, e.g. --from=G
const ARG_ONLY  = getArg('--only')?.toUpperCase() ?? null;  // single letter, e.g. --only=S
const ARG_RESET = hasArg('--reset');                         // clear all progress

// ─── Progress ─────────────────────────────────────────────────────────────────
// Saved as: { done: ['A', 'B', ...] }
// A letter is marked done only after ALL its pages are fetched.

function loadProgress() {
  if (ARG_RESET || !existsSync(PROGRESS_PATH)) return { done: [] };
  try { return JSON.parse(readFileSync(PROGRESS_PATH, 'utf8')); } catch { return { done: [] }; }
}

function saveProgress(progress) {
  writeFileSync(PROGRESS_PATH, JSON.stringify(progress, null, 2), 'utf8');
}

function saveOutput(players) {
  mkdirSync(dirname(OUTPUT_PATH), { recursive: true });
  writeFileSync(OUTPUT_PATH, JSON.stringify(players, null, 2), 'utf8');
}

function loadExistingPlayers() {
  if (ARG_RESET || !existsSync(OUTPUT_PATH)) return [];
  try { return JSON.parse(readFileSync(OUTPUT_PATH, 'utf8')); } catch { return []; }
}

// ─── FlareSolverr ─────────────────────────────────────────────────────────────

async function waitForFlareSolverr(retries = 10, delayMs = 3000) {
  for (let i = 1; i <= retries; i++) {
    try {
      const res = await fetch(FLARESOLVERR_URL.replace('/v1', '/health'));
      if (res.ok) { console.log('FlareSolverr is ready.\n'); return; }
    } catch {}
    console.log(`Waiting for FlareSolverr… (${i}/${retries})`);
    await new Promise(r => setTimeout(r, delayMs));
  }
  throw new Error('FlareSolverr did not become ready in time.');
}

async function fetchPage(url) {
  const res = await fetch(FLARESOLVERR_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ cmd: 'request.get', url, maxTimeout: 60000 }),
  });

  if (!res.ok) throw new Error(`FlareSolverr HTTP ${res.status}`);

  const json = await res.json();
  if (json.status !== 'ok') throw new Error(`FlareSolverr error: ${json.message ?? json.status}`);

  return json.solution.response;
}

// ─── Parser ───────────────────────────────────────────────────────────────────

function parsePlayers(html) {
  const root  = parse(html);
  const cards = root.querySelectorAll('.players-archive-box');
  const out   = [];

  for (const card of cards) {
    const nickname = card.querySelector('.players-archive-nickname')?.text?.trim();
    if (!nickname) continue;

    const fullName    = card.querySelector('.players-archive-name')?.text?.trim() ?? '';
    const parts       = fullName.split(' ');
    const name        = parts[0] ?? '';
    const surname     = parts.slice(1).join(' ');

    const flagImg     = card.querySelector('.players-archive-country img.flag');
    const countryName = flagImg?.getAttribute('title') ?? '';
    const flagSrc     = flagImg?.getAttribute('src') ?? '';
    const codeMatch   = flagSrc.match(/\/([A-Z]{2})\.(?:gif|png|svg|webp)/i);
    const countryCode = codeMatch ? codeMatch[1].toUpperCase() : '';

    const photoUrl = card.querySelector('.players-archive-bodyshot')?.getAttribute('src') ?? null;

    out.push({ nickname, name, surname, countryName, countryCode, photoUrl });
  }

  return out;
}

// ─── Per-letter scrape (with offset pagination) ───────────────────────────────

async function scrapeLetter(letter, letterIdx, totalLetters) {
  const letterPlayers = [];
  let offset = 0;
  let page   = 1;

  while (true) {
    // First page: BASE_URL/A  or  BASE_URL/A?offset=52
    const url = offset === 0
      ? `${BASE_URL}/${letter}`
      : `${BASE_URL}/${letter}?offset=${offset}`;

    console.log(`  [${letterIdx}/${totalLetters}] «${letter}» page ${page}: ${url}`);

    let html;
    try {
      html = await fetchPage(url);
    } catch (err) {
      console.error(`    Failed: ${err.message} — stopping pagination for «${letter}».`);
      break;
    }

    const players = parsePlayers(html);
    console.log(`    Found ${players.length} player(s).`);

    if (players.length === 0) break;  // no more pages for this letter

    letterPlayers.push(...players);

    if (players.length < OFFSET_STEP) break;  // last page (partial)

    offset += OFFSET_STEP;
    page++;
    await new Promise(r => setTimeout(r, PAGE_DELAY_MS));
  }

  return letterPlayers;
}

// ─── Main ─────────────────────────────────────────────────────────────────────

async function scrape() {
  await waitForFlareSolverr();

  if (ARG_RESET) console.log('Progress reset — starting from scratch.\n');

  const progress   = loadProgress();
  const allPlayers = loadExistingPlayers();

  if (ARG_RESET) progress.done = [];

  // Determine which letters to process
  let letters = ARG_ONLY ? [ARG_ONLY] : [...LETTERS];

  if (ARG_FROM && !ARG_ONLY) {
    const idx = letters.indexOf(ARG_FROM);
    if (idx !== -1) letters = letters.slice(idx);
    else console.warn(`Unknown letter "${ARG_FROM}", ignoring --from.\n`);
  }

  if (!ARG_ONLY && !ARG_RESET) {
    letters = letters.filter(l => !progress.done.includes(l));
  }

  if (letters.length === 0) {
    console.log('All letters already scraped. Use --reset to start over.');
    return;
  }

  console.log(`Letters to process: ${letters.join(' ')} (${letters.length})`);
  console.log(`Output:   ${OUTPUT_PATH}`);
  console.log(`Progress: ${PROGRESS_PATH}\n`);

  for (let i = 0; i < letters.length; i++) {
    const letter = letters[i];

    console.log(`\n── Letter «${letter}» (${i + 1}/${letters.length}) ──`);

    const found = await scrapeLetter(letter, i + 1, letters.length);

    allPlayers.push(...found);
    console.log(`  «${letter}» done: ${found.length} players. Total: ${allPlayers.length}.`);

    // Mark letter complete and flush to disk
    if (!progress.done.includes(letter)) progress.done.push(letter);
    saveProgress(progress);
    saveOutput(allPlayers);

    if (i < letters.length - 1) {
      console.log(`  Waiting ${PAGE_DELAY_MS}ms before next letter…`);
      await new Promise(r => setTimeout(r, PAGE_DELAY_MS));
    }
  }

  console.log(`\n✓ Done. ${allPlayers.length} total players → ${OUTPUT_PATH}`);

  const remaining = LETTERS.filter(l => !progress.done.includes(l));
  if (remaining.length > 0) {
    console.log(`Remaining: ${remaining.join(' ')}`);
    console.log('Resume with: node scrape-hltv.js');
  } else {
    console.log('All 27 letter buckets complete!');
  }
}

scrape().catch(err => {
  console.error('\nFatal error:', err.message);
  process.exit(1);
});
