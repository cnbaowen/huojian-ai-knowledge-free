<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import api from '../api/client.js'
const rows=ref([]),running=ref(false),form=reactive({question:'',expected_keyword:''})
async function load(){rows.value=(await api.get('/rag-evaluations')).data.data}
async function run(){if(!form.question.trim())return;running.value=true;try{const res=await api.post('/rag-evaluations/run',form);ElMessage[res.data.data.passed?'success':'warning'](res.data.data.passed?'评测通过':'评测未通过');form.question='';form.expected_keyword='';await load()}finally{running.value=false}}
onMounted(load)
</script>
<template><section class="page-wrap"><div class="page-head"><div><h2>RAG 评测</h2><p>用真实业务问题验证知识命中、引用来源和关键答案。</p></div></div><div class="panel"><div class="panel-head"><h3>执行单条评测</h3></div><div class="panel-body"><el-form label-position="top"><el-form-item label="测试问题"><el-input v-model="form.question" placeholder="例如：差旅报销最晚什么时候提交？"/></el-form-item><el-form-item label="期望关键词"><el-input v-model="form.expected_keyword" placeholder="例如：25日"/></el-form-item><el-button type="primary" :loading="running" @click="run">运行评测</el-button></el-form></div></div>
<div class="panel"><div class="panel-head"><h3>评测记录</h3></div><el-table :data="rows"><el-table-column prop="question" label="问题" min-width="230"/><el-table-column prop="expected_keyword" label="期望关键词" width="130"/><el-table-column label="结果" width="100"><template #default="{row}"><el-tag :type="row.passed?'success':'danger'">{{row.passed?'通过':'未通过'}}</el-tag></template></el-table-column><el-table-column prop="citations_count" label="引用" width="80"/><el-table-column prop="latency_ms" label="耗时(ms)" width="110"/><el-table-column prop="answer" label="回答" min-width="320" show-overflow-tooltip/></el-table></div></section></template>
