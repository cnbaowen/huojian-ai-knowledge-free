# 火建AI知识管理免费版

与火建AI商业版采用同一产品语言的企业知识管理免费版。它不是单页 RAG Demo，而是一套可独立部署的知识库系统：企业资料入库、分类治理、解析切片、质量检查、RAG 评测、内部知识问答、来源引用，以及企业微信群机器人配置。

> 当前版本：`0.3.0-rc.1` / `phase-6-commercial-ui-original-migration`。公开推送前仍需项目所有者确认许可证主体和品牌信息。

## 完整功能

- 商业版同款侧栏、工作台、表格、弹窗和配置交互
- 知识分类新增、编辑、删除和文档数量统计
- TXT、Markdown、LOG、CSV、JSON、DOCX、XLSX、可复制文字 PDF
- 文档上传、原文件下载、知识切片预览、索引状态和删除
- 知识质检、异常文档整改提示和质量评分
- RAG 问题评测、关键词命中、引用数和耗时记录
- 内部知识问答、分类过滤、知识不足拒答、来源引用和反馈
- 企业微信群机器人 Webhook 加密保存、地址校验和人工确认后的测试消息
- 默认本地引用式回答，可选 OpenAI 兼容模型

## 产品截图

![商业版同款工作台](docs/images/dashboard.png)

![内部知识问答](docs/images/knowledge-chat.png)

![企业知识库](docs/images/documents.png)

![企业微信机器人](docs/images/wecom-robot.png)

![登录页面](docs/images/login.png)

## 五分钟启动

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec backend php artisan migrate --force
```

打开 `http://localhost:18080`，使用 `.env` 中的 `FREE_API_TOKEN` 登录。详细步骤见 [安装说明](docs/INSTALL.md) 和 [配置说明](docs/CONFIGURATION.md)。

## 演示资料

上传 [虚构差旅制度](demo-data/company-travel-policy.md)，提问“差旅报销需要在什么时候提交？”，预期回答包含“每月 25 日前”并显示引用来源。

## 免费版边界

本仓库只包含完整知识管理、内部问答、模型配置和企业微信群机器人配置。它明确不包含 AI客服、微信客服、企微客户会话、自动回复、人工接管、CRM、内容生产、情报中心、智能体、OpenClaw、商业许可证和系统升级。

## 验收

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\acceptance.ps1
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\container-acceptance.ps1
```

Linux/macOS：

```bash
bash scripts/acceptance-linux.sh
```

## 许可证与品牌

源代码发布候选采用 [Apache License 2.0](LICENSE)。`火建AI`、`Huojian AI` 名称和品牌标识不因代码许可证而授权，详见 [NOTICE](NOTICE)。

此发布候选由独立构建器生成，生成时间：`2026-07-18T17:08:10+08:00`。
