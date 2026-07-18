<script setup>
import { computed, markRaw, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import appLogo from './assets/app-logo.png'
import { ArrowDown, ArrowLeft, ArrowRight, Bell, ChatDotRound, Close, Collection, Connection, DataAnalysis, Expand, Files, Fold, FullScreen, House, MagicStick, Monitor, Paperclip, Refresh, Setting, SwitchButton, TrendCharts, Upload } from '@element-plus/icons-vue'

const route = useRoute()
const router = useRouter()
const railCompact = ref(false)
const panelCollapsed = ref(false)
const activeGroupKey = ref(localStorage.getItem('free_active_group') || 'knowledge')

const groups = [
  { key: 'dashboard', to: '/dashboard', label: '工作台', icon: markRaw(House), hidePanel: true },
  { key: 'knowledge', label: '知识治理', icon: markRaw(Collection), children: [
    { key: 'knowledge-daily', label: '资料与问答', icon: markRaw(Upload), children: [
      { to: '/documents', label: '企业知识库', icon: markRaw(Files) },
      { to: '/categories', label: '知识分类', icon: markRaw(Collection) },
      { to: '/knowledge-chat', label: '内部知识问答', icon: markRaw(ChatDotRound) },
    ]},
    { key: 'knowledge-quality-group', label: '质量评测', icon: markRaw(DataAnalysis), children: [
      { to: '/knowledge-quality', label: '知识质检', icon: markRaw(TrendCharts) },
      { to: '/rag-evaluations', label: 'RAG 评测', icon: markRaw(DataAnalysis) },
    ]},
  ]},
  { key: 'wecom', label: '企业微信', icon: markRaw(Connection), children: [
    { to: '/wecom-configs', label: '群机器人配置', icon: markRaw(Connection) },
  ]},
  { key: 'system', label: '系统设置', icon: markRaw(Setting), children: [
    { to: '/model-configs', label: '模型配置', icon: markRaw(MagicStick) },
    { to: '/health', label: '系统状态', icon: markRaw(Monitor) },
  ]},
]

const routeGroup = computed(() => groups.find(group => group.to === route.path || group.children?.some(item => item.to === route.path || item.children?.some(child => child.to === route.path))))
const activeGroup = computed(() => routeGroup.value || groups.find(group => group.key === activeGroupKey.value) || groups[1])
const currentTitle = computed(() => route.meta.title || '工作台')
const authenticated = computed(() => route.path !== '/login' && Boolean(localStorage.getItem('free_api_token')))
const openPaths = ref(['/dashboard'])
const openTabs = computed(() => {
  const paths = [...new Set([...openPaths.value, route.path])].filter(path => path !== '/login')
  return paths.map(path => ({ path, label: path === '/dashboard' ? '工作台' : (router.resolve(path).meta.title || path), pinned: path === '/dashboard' }))
})

function activateGroup(group) {
  activeGroupKey.value = group.key
  localStorage.setItem('free_active_group', group.key)
  const target = group.to || firstLeaf(group)?.to
  if (target) router.push(target)
}
function firstLeaf(item) { return item.children ? firstLeaf(item.children[0]) : item }
function isActive(item) { return item.to === route.path }
function closeTab(tab) { if (tab.pinned) return; openPaths.value = openPaths.value.filter(path => path !== tab.path); if (tab.path === route.path) router.push('/dashboard') }
function logout() { localStorage.removeItem('free_api_token'); localStorage.removeItem('free_user'); router.push('/login') }
function toggleFullscreen() { document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen() }
</script>

<template>
  <RouterView v-if="!authenticated" />
  <div v-else class="admin-layout" :class="{ 'rail-compact': railCompact, 'panel-collapsed': panelCollapsed, 'module-panel-hidden': activeGroup.hidePanel }">
    <aside class="sidebar split-sidebar">
      <div class="module-rail">
        <div class="rail-logo"><div class="logo-mark"><img :src="appLogo" alt="火建AI" /></div></div>
        <nav class="rail-list">
          <button v-for="group in groups" :key="group.key" class="rail-item" :class="{ active: activeGroup.key === group.key }" :title="group.label" @click="activateGroup(group)">
            <span class="rail-item-inner"><el-icon class="rail-icon"><component :is="group.icon" /></el-icon><span class="rail-label">{{ group.label }}</span></span>
          </button>
        </nav>
        <button class="rail-bottom-toggle" :title="railCompact ? '展开模块菜单' : '收起模块菜单'" @click="railCompact = !railCompact"><el-icon><Fold v-if="!railCompact"/><Expand v-else/></el-icon></button>
      </div>
      <div class="module-panel">
        <div class="module-panel-head"><strong>{{ activeGroup.label }}</strong><small>火建AI知识管理免费版</small></div>
        <nav class="menu-list module-menu-list">
          <template v-for="item in activeGroup.children || []" :key="item.key || item.to">
            <div v-if="item.children" class="menu-subgroup">
              <div class="menu-subgroup-title"><el-icon><component :is="item.icon"/></el-icon><span>{{ item.label }}</span><el-icon class="menu-arrow"><ArrowDown/></el-icon></div>
              <div class="menu-subgroup-list"><RouterLink v-for="child in item.children" :key="child.to" :to="child.to" :class="{ active: isActive(child) }"><el-icon><component :is="child.icon"/></el-icon><span>{{ child.label }}</span></RouterLink></div>
            </div>
            <RouterLink v-else :to="item.to" :class="{ active: isActive(item) }"><el-icon><component :is="item.icon"/></el-icon><span>{{ item.label }}</span></RouterLink>
          </template>
        </nav>
      </div>
    </aside>

    <section class="workspace">
      <header class="app-header">
        <div class="header-left"><button class="icon-btn" :title="panelCollapsed ? '展开左侧二级菜单' : '折叠左侧二级菜单'" @click="panelCollapsed=!panelCollapsed"><el-icon><Expand v-if="panelCollapsed"/><Fold v-else/></el-icon></button><button class="icon-btn" title="刷新当前页面" @click="router.go(0)"><el-icon><Refresh/></el-icon></button><span class="breadcrumb">Dashboard</span><span class="slash">/</span><strong>{{ currentTitle }}</strong></div>
        <div class="header-actions"><el-tag type="success" effect="plain">社区免费版</el-tag><button class="icon-btn" title="全屏" @click="toggleFullscreen"><el-icon><FullScreen/></el-icon></button><button class="icon-btn notice" title="暂无待处理任务"><el-icon><Bell/></el-icon></button><el-dropdown @command="logout"><button class="user-menu"><div class="avatar">管</div><span>管理员</span><el-icon><ArrowDown/></el-icon></button><template #dropdown><el-dropdown-menu><el-dropdown-item command="logout"><el-icon><SwitchButton/></el-icon>退出登录</el-dropdown-item></el-dropdown-menu></template></el-dropdown></div>
      </header>
      <div class="tabbar"><button class="tab-scroll-btn"><el-icon><ArrowLeft/></el-icon></button><div class="tab-strip"><button v-for="tab in openTabs" :key="tab.path" class="tab" :class="{ active: tab.path === route.path, pinned: tab.pinned }" @click="router.push(tab.path)"><el-icon v-if="tab.pinned" class="pin-icon"><Paperclip/></el-icon><span>{{ tab.label }}</span><i v-if="!tab.pinned" class="tab-close" @click.stop="closeTab(tab)"><el-icon><Close/></el-icon></i></button></div><button class="tab-scroll-btn"><el-icon><ArrowRight/></el-icon></button></div>
      <main class="page-content"><RouterView /></main>
    </section>
  </div>
</template>
