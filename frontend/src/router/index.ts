import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', name: 'MainPage', component: () => import('@/views/MainPage.vue') },
    { path: '/roster/add', name: 'RosterCreator', component: () => import('@/views/RosterCreate.vue') },
    { path: '/players', name: 'Players', component: () => import('@/views/PlayersList.vue') },
    { path: '/countries', name: 'Countries', component: () => import('@/views/CountriesList.vue') },
    { path: '/matches', name: 'Matches', component: () => import('@/views/MatchesList.vue') },
  ],
})

export default router
