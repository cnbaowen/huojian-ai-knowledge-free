# 贡献指南

感谢你关注并参与火建AI知识管理免费版。

## 开始之前

- 提交新功能或较大改动前，请先搜索现有 Issue，确认没有重复讨论。
- 安全漏洞不要提交公开 Issue，请按照 [安全政策](SECURITY.md) 私密报告。
- 每个 Pull Request 尽量只解决一个明确问题，避免混入无关改动。

## 开发流程

1. 先开 Issue 说明问题或改动目标。
2. 从最新 `main` 分支创建短期功能分支。
3. 改动应保持在本免费版的知识管理与内部问答范围内，不引入本项目范围外的模块、客户数据或敏感信息。
4. 提交前运行后端语法检查、前端构建和独立验收。
5. Pull Request 中说明解决的问题、行为变化、验证证据；存在兼容性风险时说明回滚方式。

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

除非你另有明确声明，提交到本项目并拟合入项目的贡献将按照 [Apache License 2.0](LICENSE) 提供。提交前请确认你有权公开相关代码、文档、截图和数据，并且其中不包含客户资料、访问凭据或其他敏感信息。
