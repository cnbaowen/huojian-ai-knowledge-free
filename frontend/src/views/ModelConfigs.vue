<script setup>
import { onMounted, ref } from 'vue'
import api from '../api/client.js'
const config=ref(null)
onMounted(async()=>{config.value=(await api.get('/model-configs')).data.data})
</script>
<template><section class="page-wrap"><div class="page-head"><div><h2>模型配置</h2><p>查看内部问答当前使用的模型与离线运行状态。</p></div></div><div class="panel" v-if="config"><div class="panel-head"><h3>当前生效配置</h3><el-tag type="success">已生效</el-tag></div><div class="panel-body"><el-descriptions :column="2" border><el-descriptions-item label="模型提供方">{{config.provider}}</el-descriptions-item><el-descriptions-item label="模型名称">{{config.model}}</el-descriptions-item><el-descriptions-item label="离线可运行">{{config.offline_ready?'是':'否'}}</el-descriptions-item><el-descriptions-item label="外部地址已配置">{{config.base_url_configured?'是':'否'}}</el-descriptions-item><el-descriptions-item label="API密钥已配置">{{config.api_key_configured?'是':'否'}}</el-descriptions-item><el-descriptions-item label="密钥安全">接口永不返回密钥明文</el-descriptions-item></el-descriptions><el-alert title="模型参数通过服务器 .env 管理；免费版默认使用本地引用式回答，也可接入 OpenAI 兼容接口。" type="info" :closable="false" style="margin-top:18px"/></div></div></section></template>
