<template>
  <div
    class="bg-card rounded-xl overflow-hidden border transition-colors"
    :class="
      inRoster
        ? 'border-brand shadow-[0_0_0_1px_var(--color-brand)]'
        : 'border-border hover:border-brand/40'
    "
  >
    <!-- Photo -->
    <div class="relative aspect-square bg-surface overflow-hidden">
      <img
        :src="resolvePhoto(player.photo_url)"
        :alt="player.nickname"
        class="w-full h-full object-cover object-top"
      />
      <div
        v-if="inRoster"
        class="absolute top-2 right-2 bg-brand text-white rounded-full w-6 h-6 flex items-center justify-center shadow"
      >
        <CheckIcon class="w-3.5 h-3.5" />
      </div>
    </div>

    <!-- Info -->
    <div class="p-3 flex flex-col gap-2">
      <div class="flex flex-col gap-0.5">
        <p class="font-bold text-brand truncate">{{ player.nickname }}</p>
        <p class="text-sm text-gray-400 truncate">{{ player.name }} {{ player.surname }}</p>
        <span class="text-xs bg-surface text-gray-500 rounded px-2 py-0.5 self-start">
          {{ player.position }}
        </span>
      </div>

      <button
        class="w-full text-sm font-semibold py-1.5 rounded transition-colors"
        :class="
          inRoster
            ? 'bg-brand/10 text-brand hover:bg-red-500/10 hover:text-red-400 group'
            : 'bg-brand text-white hover:bg-brand-hover'
        "
        @click="emit('toggle', player)"
      >
        <span v-if="inRoster">
          <span class="group-hover:hidden">В ростере ✓</span>
          <span class="hidden group-hover:inline">Убрать</span>
        </span>
        <span v-else>+ В ростер</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { CheckIcon } from '@heroicons/vue/24/solid'
import type { Player } from '@/types/Api'
import { resolvePhoto } from '@/utils/resolvePhoto'

const props = defineProps<{
  player: Player
  inRoster: boolean
}>()

const emit = defineEmits<{
  toggle: [player: Player]
}>()
</script>
