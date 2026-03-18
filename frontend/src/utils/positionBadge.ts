import type { PlayerPosition } from '@/types/Api'

const BADGE_MAP: Record<PlayerPosition, string> = {
  AWPer: 'badge-awper',
  IGL: 'badge-igl',
  Support: 'badge-support',
  Lurker: 'badge-lurker',
  'Entry Fragger': 'badge-entry',
  Rifler: 'badge-rifler',
}

export function positionBadge(position: string): string {
  return BADGE_MAP[position as PlayerPosition] ?? 'badge-rifler'
}
