// ─── Models ───────────────────────────────────────────────────────────────────

export interface Country {
  id: number
  name: string
  code: string // ISO 3166-1 alpha-2, e.g. "RU", "UA"
  players: [Player] | null
}

export interface Player {
  id: number
  nickname: string // уникальный игровой псевдоним, e.g. "s1mple"
  name: string
  surname: string
  dateOfBirth: string // ISO 8601, e.g. "1995-06-15"
  position: string
  countryId: number
  photo_url: string | undefined // URL фото с HLTV (img-cdn.hltv.org)
}

export interface Match {
  id: number
  homeCountryId: number
  awayCountryId: number
  date: string // ISO 8601
  score: string | null // e.g. "2:1", null if not played yet
}

export interface RosterEntry {
  id: number
  matchId: number
  player_id: number
  country_id: number
}

// ─── API response wrappers ────────────────────────────────────────────────────

export interface ApiResponse<T> {
  data: T
  message?: string
}

export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

// ─── API interface ────────────────────────────────────────────────────────────

export interface Api {
  readonly baseUrl: string

  url(endpoint: string): string

  get<T>(endpoint: string): Promise<ApiResponse<T>>
  post<T>(endpoint: string, body: unknown): Promise<ApiResponse<T>>
  put<T>(endpoint: string, body: unknown): Promise<ApiResponse<T>>
  delete(endpoint: string): Promise<void>
}

// ─── Endpoint-specific interfaces ────────────────────────────────────────────

export interface CountriesApi {
  getAll(): Promise<Country[]>
  getById(id: number): Promise<Country>
  create(payload: Omit<Country, 'id'>): Promise<Country>
  update(id: number, payload: Partial<Omit<Country, 'id'>>): Promise<Country>
  remove(id: number): Promise<void>
}

export interface PlayersApi {
  getAll(): Promise<Player[]>
  getById(id: number): Promise<Player>
  create(payload: Omit<Player, 'id'>): Promise<Player>
  update(id: number, payload: Partial<Omit<Player, 'id'>>): Promise<Player>
  remove(id: number): Promise<void>
}

export interface MatchesApi {
  getAll(): Promise<Match[]>
  getById(id: number): Promise<Match>
  create(payload: Omit<Match, 'id'>): Promise<Match>
  update(id: number, payload: Partial<Omit<Match, 'id'>>): Promise<Match>
  remove(id: number): Promise<void>
}

export interface RosterApi {
  getByMatch(matchId: number): Promise<RosterEntry[]>
  add(payload: Omit<RosterEntry, 'id'>): Promise<RosterEntry>
  remove(id: number): Promise<void>
}
