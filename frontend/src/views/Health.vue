<script setup>
import { onMounted, ref } from 'vue'
import api from '../api/client.js'
const state=ref({status:'checking',edition:'-',stage:'-'})
onMounted(async()=>{try{state.value=(await api.get('/health')).data}catch{state.value.status='unavailable'}})
</script>
<template><section class="page-wrap"><div class="page-head"><div><h2>系统状态</h2><p>免费版运行环境和发布边界状态。</p></div></div><div class="metric-grid"><article class="metric-card"><div class="metric-label">API服务</div><div class="metric-value" style="font-size:20px"><span :class="['status-dot',state.status==='ok'?'':'error']"></span>{{state.status}}</div></article><article class="metric-card"><div class="metric-label">产品版本</div><div class="metric-value" style="font-size:20px">{{state.edition}}</div></article><article class="metric-card"><div class="metric-label">构建阶段</div><div class="metric-value" style="font-size:16px">{{state.stage}}</div></article></div></section></template>
