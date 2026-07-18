<script setup>
import { onMounted, ref } from 'vue'
import api from '../api/client.js'
const data = ref({})
onMounted(async () => { data.value = (await api.get('/dashboard')).data.data })
const metrics = [
  ['documents','知识文档','已进入知识库的文档总量'],['chunks','知识切片','可参与检索的内容单元'],['categories','知识分类','企业知识结构'],['questions','问答次数','内部知识问答累计'],
]
</script>
<template><section class="page-wrap">
  <div class="page-head"><div><h2>工作台</h2><p>知识资产、检索质量和企业微信机器人状态总览</p></div><el-tag type="success">服务正常</el-tag></div>
  <div class="metric-grid"><article v-for="m in metrics" :key="m[0]" class="metric-card"><div class="metric-label">{{m[1]}}</div><div class="metric-value">{{data[m[0]] ?? 0}}</div><div class="metric-note">{{m[2]}}</div></article></div>
  <div class="panel" style="margin-top:16px"><div class="panel-head"><h3>知识库运行情况</h3></div><div class="panel-body metric-grid">
    <div><div class="metric-label">已完成索引</div><div class="metric-value">{{data.indexed_documents ?? 0}}</div></div>
    <div><div class="metric-label">回答引用率</div><div class="metric-value">{{data.citation_rate ?? 0}}%</div></div>
    <div><div class="metric-label">企微机器人</div><div class="metric-value" style="font-size:20px"><span :class="['status-dot',data.wecom_enabled?'':'warn']"></span>{{data.wecom_enabled?'已启用':'未启用'}}</div></div>
  </div></div>
</section></template>
