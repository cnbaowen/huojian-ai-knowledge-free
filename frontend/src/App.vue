<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ChatDotRound, Collection, DataAnalysis, Files, House, Monitor, Setting, SwitchButton, TrendCharts, Upload, Connection } from '@element-plus/icons-vue'

const route = useRoute()
const router = useRouter()
const authenticated = computed(() => route.path !== '/login' && Boolean(localStorage.getItem('free_api_token')))
const groups = [
  { label: '知识治理', children: [
    { to: '/documents', label: '企业知识库', icon: Files },
    { to: '/categories', label: '知识分类', icon: Collection },
    { to: '/knowledge-quality', label: '知识质检', icon: TrendCharts },
    { to: '/rag-evaluations', label: 'RAG 评测', icon: DataAnalysis },
    { to: '/knowledge-chat', label: '内部知识问答', icon: ChatDotRound },
  ]},
  { label: '企业微信', children: [
    { to: '/wecom-configs', label: '企微机器人', icon: Connection },
  ]},
  { label: '系统设置', children: [
    { to: '/model-configs', label: '模型配置', icon: Setting },
    { to: '/health', label: '系统状态', icon: Monitor },
  ]},
]
const title = computed(() => route.meta.title || '工作台')
function logout() { localStorage.removeItem('free_api_token'); router.push('/login') }
</script>

<template>
  <div v-if="authenticated" class="enterprise-shell">
    <aside class="enterprise-sidebar">
      <div class="brand-block"><div class="brand-mark">火</div><div><strong>火建AI</strong><small>知识管理免费版</small></div></div>
      <RouterLink class="home-link" to="/dashboard"><el-icon><House /></el-icon><span>工作台</span></RouterLink>
      <section v-for="group in groups" :key="group.label" class="nav-group">
        <div class="nav-group-title">{{ group.label }}</div>
        <RouterLink v-for="item in group.children" :key="item.to" :to="item.to"><el-icon><component :is="item.icon" /></el-icon><span>{{ item.label }}</span></RouterLink>
      </section>
      <button class="logout-button" @click="logout"><el-icon><SwitchButton /></el-icon><span>退出登录</span></button>
    </aside>
    <div class="enterprise-main">
      <header class="topbar"><div><h1>{{ title }}</h1><p>企业知识资产治理与内部问答</p></div><el-tag type="success" effect="light">社区免费版</el-tag></header>
      <main class="workspace"><RouterView /></main>
    </div>
  </div>
  <RouterView v-else />
</template>
