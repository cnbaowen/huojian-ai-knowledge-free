<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api/client.js'
const token=ref(''),error=ref(''),busy=ref(false),router=useRouter()
async function login(){busy.value=true;error.value='';try{const res=await api.post('/login',{token:token.value});localStorage.setItem('free_api_token',res.data.token);router.push('/dashboard')}catch(e){error.value=e.response?.data?.message||'暂时无法登录'}finally{busy.value=false}}
</script>
<template><div class="login-page"><section class="login-card"><div class="login-logo"><div class="brand-mark">火</div><div><h1>火建AI</h1><p style="margin:4px 0 0">知识管理免费版</p></div></div><p>企业知识资产治理、内部知识问答与企微机器人配置。</p><el-form label-position="top" @submit.prevent="login"><el-form-item label="访问令牌"><el-input v-model="token" type="password" show-password autocomplete="current-password" @keyup.enter="login"/></el-form-item><el-alert v-if="error" :title="error" type="error" :closable="false" style="margin-bottom:16px"/><el-button type="primary" size="large" style="width:100%" :loading="busy" :disabled="!token" @click="login">登录系统</el-button></el-form></section></div></template>
