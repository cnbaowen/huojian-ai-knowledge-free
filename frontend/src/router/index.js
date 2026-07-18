import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'

const routes = [
  { path: '/', redirect: '/dashboard' },
  { path: '/login', component: Login, meta: { title: '登录' } },
  { path: '/dashboard', component: () => import('../views/Dashboard.vue'), meta: { title: '工作台' } },
  { path: '/documents', component: () => import('../views/Documents.vue'), meta: { title: '企业知识库' } },
  { path: '/categories', component: () => import('../views/Categories.vue'), meta: { title: '知识分类' } },
  { path: '/knowledge-quality', component: () => import('../views/KnowledgeQuality.vue'), meta: { title: '知识质检' } },
  { path: '/rag-evaluations', component: () => import('../views/RagEvaluations.vue'), meta: { title: 'RAG 评测' } },
  { path: '/knowledge-chat', component: () => import('../views/KnowledgeChat.vue'), meta: { title: '内部知识问答' } },
  { path: '/wecom-configs', component: () => import('../views/WeComConfigs.vue'), meta: { title: '企业微信机器人' } },
  { path: '/model-configs', component: () => import('../views/ModelConfigs.vue'), meta: { title: '模型配置' } },
  { path: '/health', component: () => import('../views/Health.vue'), meta: { title: '系统状态' } },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
]
const router = createRouter({ history: createWebHistory(), routes })
router.beforeEach(to => {
  const token = localStorage.getItem('free_api_token')
  if (to.path !== '/login' && !token) return '/login'
  if (to.path === '/login' && token) return '/dashboard'
})
export function preloadRouteComponent(path) { const matched = router.resolve(path).matched; matched.forEach(record => typeof record.components?.default === 'function' && record.components.default()) }
export default router
