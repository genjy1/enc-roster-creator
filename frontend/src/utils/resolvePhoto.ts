const HLTV_SILHOUETTE = '/img/static/player/player_silhouette.png'

const PLACEHOLDER_IMG =
  `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E` +
  `%3Crect width='100' height='100' fill='%23141517'/%3E` +
  `%3Ccircle cx='50' cy='35' r='18' fill='%232a2d31'/%3E` +
  `%3Cellipse cx='50' cy='85' rx='30' ry='22' fill='%232a2d31'/%3E` +
  `%3C/svg%3E`

export function resolvePhoto(url: string | undefined | null): string {
  if (!url || url === HLTV_SILHOUETTE) return PLACEHOLDER_IMG
  return url
}
