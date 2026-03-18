<template>
  <div class="relative" ref="rootEl">
    <!-- Trigger -->
    <button
      type="button"
      class="flex items-center justify-between gap-3 w-full bg-card border text-sm px-4 py-2.5 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-brand"
      :class="open ? 'border-brand' : 'border-border hover:border-gray-500'"
      @click="toggle"
    >
      <span class="flex items-center gap-2" :class="modelValue ? 'text-gray-200' : 'text-gray-500'">
        <CalendarIcon class="w-4 h-4 shrink-0" />
        {{ displayValue }}
      </span>
      <ChevronDownIcon
        class="w-4 h-4 text-gray-500 shrink-0 transition-transform duration-200"
        :class="{ 'rotate-180': open }"
      />
    </button>

    <!-- Calendar dropdown -->
    <Transition name="dropdown">
      <div
        v-if="open"
        class="absolute z-50 mt-1 w-72 bg-card border border-border rounded-xl shadow-xl overflow-hidden"
      >
        <!-- Month / year navigation -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-border">
          <button type="button" class="nav-btn" @click="prevMonth">
            <ChevronLeftIcon class="w-4 h-4" />
          </button>

          <!-- Clickable month + year -->
          <button
            type="button"
            class="text-sm font-bold text-white hover:text-brand transition-colors capitalize"
            @click="view = view === 'days' ? 'months' : 'days'"
          >
            {{ monthLabel }} {{ viewYear }}
          </button>

          <button type="button" class="nav-btn" @click="nextMonth">
            <ChevronRightIcon class="w-4 h-4" />
          </button>
        </div>

        <!-- Month picker -->
        <div v-if="view === 'months'" class="p-3 grid grid-cols-3 gap-1.5">
          <button
            v-for="(name, idx) in MONTH_NAMES"
            :key="idx"
            type="button"
            class="text-xs py-2 rounded-lg transition-colors font-medium"
            :class="
              idx === viewMonth
                ? 'bg-brand text-black font-bold'
                : 'text-gray-400 hover:bg-white/5 hover:text-white'
            "
            @click="selectMonth(idx)"
          >
            {{ name }}
          </button>
        </div>

        <!-- Day picker -->
        <div v-else class="p-3">
          <!-- Weekday headers -->
          <div class="grid grid-cols-7 mb-1">
            <span
              v-for="d in WEEKDAYS"
              :key="d"
              class="text-center text-[10px] font-semibold text-gray-600 uppercase py-1"
              >{{ d }}</span
            >
          </div>

          <!-- Day grid -->
          <div class="grid grid-cols-7 gap-y-0.5">
            <button
              v-for="cell in cells"
              :key="cell.key"
              type="button"
              :disabled="cell.outside"
              class="relative aspect-square flex items-center justify-center text-xs rounded-lg transition-colors"
              :class="dayClass(cell)"
              @click="selectDay(cell)"
            >
              <!-- Today dot -->
              <span
                v-if="cell.isToday && !cell.isSelected"
                class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-brand"
              />
              {{ cell.day }}
            </button>
          </div>
        </div>

        <!-- Footer actions -->
        <div class="flex items-center justify-between px-3 py-2 border-t border-border">
          <button
            type="button"
            class="text-xs text-gray-500 hover:text-gray-300 transition-colors"
            @click="goToday"
          >
            Сегодня
          </button>
          <button
            v-if="modelValue"
            type="button"
            class="text-xs text-gray-500 hover:text-red-400 transition-colors"
            @click="clear"
          >
            Очистить
          </button>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { onClickOutside } from '@vueuse/core'
import { CalendarIcon } from '@heroicons/vue/24/outline'
import { ChevronDownIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/solid'

const WEEKDAYS = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
const MONTH_NAMES = [
  'Январь',
  'Февраль',
  'Март',
  'Апрель',
  'Май',
  'Июнь',
  'Июль',
  'Август',
  'Сентябрь',
  'Октябрь',
  'Ноябрь',
  'Декабрь',
]
const MONTH_NAMES_GEN = [
  'января',
  'февраля',
  'марта',
  'апреля',
  'мая',
  'июня',
  'июля',
  'августа',
  'сентября',
  'октября',
  'ноября',
  'декабря',
]

const props = withDefaults(
  defineProps<{
    modelValue: string // ISO: YYYY-MM-DD or ''
    placeholder?: string
  }>(),
  { placeholder: 'Выберите дату' },
)

const emit = defineEmits<{ 'update:modelValue': [value: string] }>()

const rootEl = ref<HTMLElement | null>(null)
const open = ref(false)
type View = 'days' | 'months'
const view = ref<View>('days')

// Viewing state — month/year being browsed
const today = new Date()
const viewYear = ref(today.getFullYear())
const viewMonth = ref(today.getMonth())

// Sync view to current modelValue when picker opens
watch(open, (val) => {
  if (!val) return
  view.value = 'days'
  if (props.modelValue) {
    const d = parseISO(props.modelValue)
    if (d) {
      viewYear.value = d.getFullYear()
      viewMonth.value = d.getMonth()
      return
    }
  }
  viewYear.value = today.getFullYear()
  viewMonth.value = today.getMonth()
})

function parseISO(iso: string): Date | null {
  const m = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/)
  if (!m) return null
  return new Date(+m[1], +m[2] - 1, +m[3])
}

function toISO(d: Date): string {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

const displayValue = computed(() => {
  if (!props.modelValue) return props.placeholder
  const d = parseISO(props.modelValue)
  if (!d) return props.placeholder
  return `${d.getDate()} ${MONTH_NAMES_GEN[d.getMonth()]} ${d.getFullYear()}`
})

const monthLabel = computed(() => MONTH_NAMES[viewMonth.value])

interface DayCell {
  key: string
  day: number
  date: Date
  outside: boolean
  isToday: boolean
  isSelected: boolean
}

const cells = computed<DayCell[]>(() => {
  const year = viewYear.value
  const month = viewMonth.value

  // First day of month (0=Sun…6=Sat → convert to Mon-based 0–6)
  const firstDow = new Date(year, month, 1).getDay()
  const offset = (firstDow + 6) % 7 // Mon=0

  const daysInMonth = new Date(year, month + 1, 0).getDate()
  const daysInPrev = new Date(year, month, 0).getDate()

  const selected = props.modelValue ? parseISO(props.modelValue) : null

  const result: DayCell[] = []

  // Leading days from previous month
  for (let i = offset - 1; i >= 0; i--) {
    const d = new Date(year, month - 1, daysInPrev - i)
    result.push(makeCell(d, true, selected))
  }

  // Current month days
  for (let i = 1; i <= daysInMonth; i++) {
    const d = new Date(year, month, i)
    result.push(makeCell(d, false, selected))
  }

  // Trailing days to fill last row
  const remaining = 7 - (result.length % 7)
  if (remaining < 7) {
    for (let i = 1; i <= remaining; i++) {
      const d = new Date(year, month + 1, i)
      result.push(makeCell(d, true, selected))
    }
  }

  return result
})

function makeCell(d: Date, outside: boolean, selected: Date | null): DayCell {
  const iso = toISO(d)
  return {
    key: iso,
    day: d.getDate(),
    date: d,
    outside,
    isToday: iso === toISO(today),
    isSelected: !!selected && iso === toISO(selected),
  }
}

function dayClass(cell: DayCell): string {
  if (cell.isSelected) return 'bg-brand text-black font-bold hover:bg-brand-hover'
  if (cell.outside) return 'text-gray-700 cursor-default'
  return 'text-gray-300 hover:bg-white/5'
}

function toggle(): void {
  open.value = !open.value
}

function close(): void {
  open.value = false
}

function prevMonth(): void {
  if (viewMonth.value === 0) {
    viewMonth.value = 11
    viewYear.value--
  } else viewMonth.value--
}

function nextMonth(): void {
  if (viewMonth.value === 11) {
    viewMonth.value = 0
    viewYear.value++
  } else viewMonth.value++
}

function selectMonth(idx: number): void {
  viewMonth.value = idx
  view.value = 'days'
}

function selectDay(cell: DayCell): void {
  if (cell.outside) return
  emit('update:modelValue', toISO(cell.date))
  close()
}

function goToday(): void {
  const iso = toISO(today)
  emit('update:modelValue', iso)
  viewYear.value = today.getFullYear()
  viewMonth.value = today.getMonth()
  close()
}

function clear(): void {
  emit('update:modelValue', '')
  close()
}

onClickOutside(rootEl, close)
</script>

<style scoped>
.nav-btn {
  @reference '../main.css';
  display: flex;
  align-items: center;
  justify-content: center;
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.5rem;
  color: var(--color-gray-500, #6b7280);
  transition:
    background-color 0.15s,
    color 0.15s;
}

.nav-btn:hover {
  background-color: rgb(255 255 255 / 0.05);
  color: #e5e7eb;
}

.dropdown-enter-active,
.dropdown-leave-active {
  transition:
    opacity 0.15s ease,
    transform 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-4px);
}
</style>
