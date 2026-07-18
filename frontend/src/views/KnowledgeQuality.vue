<script setup>
import { onMounted, ref } from 'vue'
import { Refresh } from '@element-plus/icons-vue'
import api from '../api/client.js'
const data=ref({issues:[]})
async function load(){data.value=(await api.get('/knowledge-quality')).data.data}
onMounted(load)
</script>
<template><section class="page-wrap"><div class="page-head"><div><h2>知识质检</h2><p>持续检查分类、解析和切片质量，避免低质量资料影响内部问答。</p></div><el-button :icon="Refresh" @click="load">重新检查</el-button></div>
<div class="metric-grid"><article class="metric-card"><div class="metric-label">知识质量分</div><div class="metric-value">{{data.quality_score ?? 100}}</div><div class="metric-note">满分 100</div></article><article class="metric-card"><div class="metric-label">文档总数</div><div class="metric-value">{{data.documents ?? 0}}</div></article><article class="metric-card"><div class="metric-label">健康文档</div><div class="metric-value">{{data.healthy_documents ?? 0}}</div></article><article class="metric-card"><div class="metric-label">待整改项</div><div class="metric-value">{{data.issues_count ?? 0}}</div></article></div>
<div class="panel" style="margin-top:16px"><div class="panel-head"><h3>质检问题</h3></div><el-table :data="data.issues||[]"><el-table-column prop="document_name" label="文档" min-width="240"/><el-table-column prop="category" label="分类" width="160"><template #default="{row}">{{row.category||'未分类'}}</template></el-table-column><el-table-column label="问题"><template #default="{row}"><el-tag v-for="reason in row.reasons" :key="reason" type="warning" style="margin-right:7px">{{reason}}</el-tag></template></el-table-column></el-table><div v-if="!(data.issues||[]).length" class="empty-state">当前知识库没有发现质量问题</div></div></section></template>
