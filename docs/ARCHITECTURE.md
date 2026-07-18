# 架构边界

```text
Vue 3 + Element Plus 产品界面
  -> Laravel API / 令牌鉴权
     -> 知识分类、文档管理、原文下载与切片预览
     -> TXT / Markdown / CSV / JSON / DOCX / XLSX / PDF 文本提取
     -> 知识质检与 RAG 评测
     -> 企业微信群机器人配置（Webhook 加密保存）
     -> 重叠切片
        -> 256 维本地向量 + MySQL/SQLite
     -> 混合检索
        -> 引用约束回答器
           -> 本地抽取模式
           -> OpenAI 兼容模式（可选）
```

免费版使用自己的数据库、网络、容器和模型配置。AI客服、微信客服、企微会话、CRM、内容生产、情报、智能体、OpenClaw、许可证与系统升级模块不在本项目范围内，在源码和路由层均不存在。

Compose 使用独立项目名 `huojian-ai-knowledge-free`、独立网络、独立 MySQL 数据卷和独立文档卷。
