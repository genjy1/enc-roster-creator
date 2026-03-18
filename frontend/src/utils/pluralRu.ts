/**
 * Возвращает окончание слова «игрок» для числа n по правилам русской грамматики.
 * Пример: 1 → '', 2 → 'а', 11 → 'ов'
 */
export function playerSuffix(n: number): string {
  const mod10 = n % 10
  const mod100 = n % 100
  if (mod100 >= 11 && mod100 <= 14) return 'ов'
  if (mod10 === 1) return ''
  if (mod10 >= 2 && mod10 <= 4) return 'а'
  return 'ов'
}
