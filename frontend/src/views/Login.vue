<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../api/client.js'
import appLogo from '../assets/app-logo.png'
const account=ref('admin@free.local'),token=ref(''),error=ref(''),busy=ref(false),router=useRouter()
async function login(){busy.value=true;error.value='';try{const res=await api.post('/login',{token:token.value});localStorage.setItem('free_api_token',res.data.token);localStorage.setItem('free_user',JSON.stringify(res.data.user||{}));router.push('/dashboard')}catch(e){error.value=e.response?.data?.message||'暂时无法登录'}finally{busy.value=false}}
</script>
<template><div class="login-commercial"><section class="login-shell"><div class="login-showcase"><img class="login-brand-logo" :src="appLogo" alt="火建AI"><h1>火建AI<br>知识管理免费版</h1><p>让企业资料完成上传、解析、切片、检索与内部问答，<br>把分散知识沉淀为可引用、可验证的知识资产。</p><div class="login-illustration"></div></div><div class="login-form-side"><span class="eyebrow">管理员登录</span><h2>进入系统后台</h2><p>使用部署时配置的免费版访问令牌登录。</p><el-form label-position="top" @submit.prevent="login"><el-form-item label="登录账号"><el-input v-model="account" disabled/></el-form-item><el-form-item label="访问令牌"><el-input v-model="token" type="password" show-password autocomplete="current-password" placeholder="请输入访问令牌" @keyup.enter="login"/></el-form-item><el-alert v-if="error" :title="error" type="error" :closable="false" style="margin-bottom:16px"/><el-button class="login-submit" type="primary" :loading="busy" :disabled="!token" @click="login">进入系统后台</el-button></el-form><div class="login-hint">管理账号　<strong>admin@free.local</strong>　/　部署时设置的令牌</div></div></section></div></template>
