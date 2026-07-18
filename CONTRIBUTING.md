# 贡献指南

感谢你关注并参与火建AI知识管理免费版。

## 开始之前

- 提交新功能或较大改动前，请先搜索现有 Issue，确认没有重复讨论。
- 安全漏洞不要提交公开 Issue，请按照 [安全政策](SECURITY.md) 私密报告。
- 每个 Pull Request 尽量只解决一个明确问题，避免混入无关改动。

## 开发流程

1. 先开 Issue 说明问题或改动目标。
2. 从最新 `main` 分支创建短期功能分支。
3. 保持改动聚焦，较大的新功能建议先在 Issue 中讨论实现方向。
4. 提交前运行后端语法检查、前端构建和项目验收脚本。
5. 在 Pull Request 中说明解决的问题、主要改动和验证结果。

## 本地检查

```bash
find backend/app backend/bootstrap backend/config backend/database backend/public backend/routes \
  -name '*.php' -print0 | xargs -0 -n1 php -l
composer --working-dir=backend validate --no-check-publish
npm --prefix frontend ci
npm --prefix frontend run build
bash scripts/acceptance-linux.sh
```

Windows PowerShell：

```powershell
$phpRoots = @('backend/app', 'backend/bootstrap', 'backend/config', 'backend/database', 'backend/public', 'backend/routes')
Get-ChildItem $phpRoots -Recurse -Filter *.php | ForEach-Object {
    php -l $_.FullName
    if ($LASTEXITCODE -ne 0) { throw "PHP syntax check failed: $($_.FullName)" }
}
composer --working-dir=backend validate --no-check-publish
npm --prefix frontend ci
npm --prefix frontend run build
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\acceptance.ps1
```

## Pull Request 要求

- 标题简洁说明改动目的。
- 正文说明改动前后的行为差异和验证结果。
- 页面改动请提供截图；接口改动请提供请求和响应示例。
- 不得提交 `.env`、密钥、Webhook、数据库、日志、客户资料或构建产物。

## 贡献许可

除非你另有明确声明，提交到本项目并计划合并的贡献将按照 [Apache License 2.0](LICENSE) 提供。提交前请确认你有权公开相关代码、文档、截图和数据，并且其中不包含个人隐私、客户资料、访问凭据或其他敏感信息。
