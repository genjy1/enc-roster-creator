// ─── Domain types ─────────────────────────────────────────────────────────────

export type PlayerPosition = 'AWPer' | 'IGL' | 'Rifler' | 'Support' | 'Lurker' | 'Entry Fragger'

export interface Country {
  id: number
  name: string
  code: string // ISO 3166-1 alpha-2, e.g. "RU", "UA"
  players: Player[] | null
}

export interface Player {
  id: number
  nickname: string // уникальный игровой псевдоним, e.g. "s1mple"
  name: string
  surname: string
  date_of_birth: string // ISO 8601
  position: PlayerPosition
  country_id: number
  country_code: string | null
  country_name: string | null
  photo_url: string | null
}

export interface Match {
  id: number
  home_country_id: number
  away_country_id: number
  date: string // ISO 8601
  score: string | null // e.g. "2:1", null if not played yet
}

export interface RosterEntry {
  id: number
  match_id: number
  player_id: number
  country_id: number
}

export interface RosterGroup {
  id: number
  name: string
  code: string
  players: Player[]
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
