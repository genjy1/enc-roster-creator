import { parse } from 'node-html-parser';
import { writeFileSync, mkdirSync } from 'fs';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));

const BASE_URL         = 'https://www.hltv.org/players';
const OFFSET_STEP      = 52;
const OFFSET_MAX       = 312;
const PAGE_DELAY_MS    = 3000;
const FLARESOLVERR_URL = (process.env.FLARESOLVERR_URL ?? 'http://localhost:8191') + '/v1';
const OUTPUT_PATH      = process.env.OUTPUT_PATH
  ?? resolve(__dirname, '../backend/storage/app/players.json');

async function fetchViaFlareSolverr(url) {
  const res = await fetch(FLARESOLVERR_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      cmd: 'request.get',
      url,
      maxTimeout: 60000,
    }),
  });

  if (!res.ok) {
    throw new Error(`FlareSolverr HTTP ${res.status}`);
  }

  const json = await res.json();

  if (json.status !== 'ok') {
    throw new Error(`FlareSolverr error: ${json.message ?? json.status}`);
  }

  return json.solution.response;
}

function parsePlayers(html) {
  const root    = parse(html);
  const cards   = root.querySelectorAll('.players-archive-box');
  const players = [];

  for (const card of cards) {
    const nickname = card.querySelector('.players-archive-nickname')?.text?.trim();
    if (!nickname) continue;

    const fullName = card.querySelector('.players-archive-name')?.text?.trim() ?? '';
    const parts    = fullName.split(' ');
    const name     = parts[0] ?? '';
    const surname  = parts.slice(1).join(' ');

    const flagImg     = card.querySelector('.players-archive-country img.flag');
    const countryName = flagImg?.getAttribute('title') ?? '';
    const flagSrc     = flagImg?.getAttribute('src') ?? '';
    const codeMatch   = flagSrc.match(/\/([A-Z]{2})\.(?:gif|png|svg|webp)/i);
    const countryCode = codeMatch ? codeMatch[1].toUpperCase() : '';

    // Photo: <img class="players-archive-bodyshot" src="https://img-cdn.hltv.org/...">
    const photoUrl = card.querySelector('.players-archive-bodyshot')?.getAttribute('src') ?? null;

    players.push({ nickname, name, surname, countryName, countryCode, photoUrl });
  }

  return players;
}

function save(players) {
  mkdirSync(dirname(OUTPUT_PATH), { recursive: true });
  writeFileSync(OUTPUT_PATH, JSON.stringify(players, null, 2), 'utf8');
}

async function waitForFlareSolverr(retries = 10, delayMs = 3000) {
  for (let i = 1; i <= retries; i++) {
    try {
      const res = await fetch(FLARESOLVERR_URL.replace('/v1', '/health'));
      if (res.ok) { console.log('FlareSolverr is ready.'); return; }
    } catch {}
    console.log(`Waiting for FlareSolverr… (${i}/${retries})`);
    await new Promise(r => setTimeout(r, delayMs));
  }
  throw new Error('FlareSolverr did not become ready in time.');
}

async function scrape() {
  await waitForFlareSolverr();

  const allPlayers = [];
  const totalPages = OFFSET_MAX / OFFSET_STEP + 1;

  for (let offset = 0; offset <= OFFSET_MAX; offset += OFFSET_STEP) {
    const pageNum = offset / OFFSET_STEP + 1;
    const url     = `${BASE_URL}?offset=${offset}`;

    console.log(`Fetching page ${pageNum}/${totalPages}: ${url}`);

    let html;
    try {
      html = await fetchViaFlareSolverr(url);
    } catch (err) {
      console.error(`  Page ${pageNum} failed: ${err.message}`);
      if (allPlayers.length) save(allPlayers);
      continue;
    }

    const players = parsePlayers(html);

    if (players.length === 0) {
      console.warn(`  No players parsed on page ${pageNum} — HTML structure may have changed.`);
      continue;
    }

    console.log(`  Found ${players.length} players.`);
    allPlayers.push(...players);

    if (offset < OFFSET_MAX) {
      await new Promise(r => setTimeout(r, PAGE_DELAY_MS));
    }
  }

  save(allPlayers);
  console.log(`\nDone. ${allPlayers.length} players saved to ${OUTPUT_PATH}`);
}

scrape().catch(err => {
  console.error('Fatal:', err);
  process.exit(1);
});
