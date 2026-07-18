<script setup>
import { onMounted, reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import api from '../api/client.js'
const rows=ref([]), visible=ref(false), editing=ref(null), form=reactive({name:''})
async function load(){rows.value=(await api.get('/categories')).data.data}
function open(row=null){editing.value=row;form.name=row?.name||'';visible.value=true}
async function save(){if(!form.name.trim())return; editing.value?await api.put(`/categories/${editing.value.id}`,{name:form.name.trim()}):await api.post('/categories',{name:form.name.trim()});ElMessage.success('保存成功');visible.value=false;await load()}
async function remove(row){await ElMessageBox.confirm(`确认删除分类“${row.name}”？`,'删除分类',{type:'warning'});await api.delete(`/categories/${row.id}`);ElMessage.success('已删除');await load()}
onMounted(load)
</script>
<template><section class="page-wrap"><div class="page-head"><div><h2>知识分类</h2><p>建立与企业业务一致的知识目录，文档可按分类限定检索范围。</p></div><el-button type="primary" @click="open()">新增分类</el-button></div>
<div class="panel"><el-table :data="rows"><el-table-column prop="name" label="分类名称" min-width="260"/><el-table-column prop="documents_count" label="文档数量" width="140"/><el-table-column label="状态" width="130"><template #default><el-tag type="success">启用</el-tag></template></el-table-column><el-table-column label="操作" width="170"><template #default="{row}"><el-button link type="primary" @click="open(row)">编辑</el-button><el-button link type="danger" @click="remove(row)">删除</el-button></template></el-table-column></el-table><div v-if="!rows.length" class="empty-state">尚未创建知识分类</div></div>
<el-dialog v-model="visible" :title="editing?'编辑知识分类':'新增知识分类'" width="480"><el-form label-position="top"><el-form-item label="分类名称"><el-input v-model="form.name" maxlength="100" show-word-limit/></el-form-item></el-form><template #footer><el-button @click="visible=false">取消</el-button><el-button type="primary" @click="save">保存</el-button></template></el-dialog></section></template>
