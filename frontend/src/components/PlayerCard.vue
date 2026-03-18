<template>
  <div
    class="bg-card rounded-xl overflow-hidden border transition-all duration-200 group/card"
    :class="
      inRoster
        ? 'border-brand glow-brand-sm scale-[1.02]'
        : 'border-border hover:border-border-bright hover:-translate-y-1 hover:shadow-xl'
    "
  >
    <!-- Photo -->
    <div class="relative aspect-square bg-surface-2 overflow-hidden">
      <img
        :src="resolvePhoto(player.photo_url)"
        :alt="player.nickname"
        class="w-full h-full object-cover object-top transition-transform duration-300 group-hover/card:scale-105"
      />
      <!-- Bottom fade -->
      <div
        class="absolute inset-x-0 bottom-0 h-1/2"
        style="background: linear-gradient(to top, #161a1f, transparent)"
      />
      <!-- In-roster check -->
      <div
        v-if="inRoster"
        class="absolute top-2 right-2 bg-brand text-black rounded-full w-6 h-6 flex items-center justify-center shadow glow-brand-sm"
      >
        <CheckIcon class="w-3.5 h-3.5" />
      </div>
      <!-- Position badges overlaid on photo -->
      <div class="absolute bottom-2 left-2 flex flex-wrap gap-1">
        <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', primaryBadge]">
          {{ player.primary_position }}
        </span>
        <span
          v-if="player.secondary_position"
          :class="['text-xs font-semibold px-2 py-0.5 rounded-full opacity-80', secondaryBadge]"
        >
          {{ player.secondary_position }}
        </span>
      </div>
    </div>

    <!-- Info -->
    <div class="p-3 flex flex-col gap-2">
      <div>
        <p class="font-bold text-brand truncate text-sm">{{ player.nickname }}</p>
        <p class="text-xs text-gray-500 truncate mt-0.5">{{ player.name }} {{ player.surname }}</p>
      </div>

      <button
        class="w-full text-sm font-bold py-1.5 rounded-lg transition-all duration-150"
        :class="
          inRoster
            ? 'bg-brand/10 text-brand border border-brand/30 hover:bg-red-500/10 hover:text-red-400 hover:border-red-500/30 group'
            : 'bg-brand hover:bg-brand-hover text-black'
        "
        @click="emit('toggle', player)"
      >
        <template v-if="inRoster">
          <span class="group-hover:hidden">✓ В ростере</span>
          <span class="hidden group-hover:inline">− Убрать</span>
        </template>
        <template v-else>+ В ростер</template>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { CheckIcon } from '@heroicons/vue/24/solid'
import type { Player } from '@/types/Api'
import { resolvePhoto } from '@/utils/resolvePhoto'
import { positionBadge as getBadge } from '@/utils/positionBadge'

const props = defineProps<{
  player: Player
  inRoster: boolean
}>()

const emit = defineEmits<{
  toggle: [player: Player]
}>()

const primaryBadge = computed(() => getBadge(props.player.primary_position))
const secondaryBadge = computed(() => getBadge(props.player.secondary_position ?? 'Rifler'))
</script>
