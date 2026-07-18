# 贡献指南

感谢参与火建AI企业知识问答免费版。

## 开发流程

1. 先开 Issue 说明问题或改动目标。
2. 从最新主分支创建短分支。
3. 保持改动只覆盖内部知识问答免费版，不引入商业模块或客户数据。
4. 提交前运行后端语法检查、前端构建和独立验收。
5. Pull Request 中说明行为变化、验证证据和回滚方式。

## 本地检查

```bash
find backend -name '*.php' -print0 | xargs -0 -n1 php -l
composer --working-dir=backend validate --no-check-publish
npm --prefix frontend ci
npm --prefix frontend run build
bash scripts/acceptance-linux.sh
```

提交贡献即表示你同意按项目的 Apache-2.0 许可证提供该贡献。不要提交你无权公开的代码、文档、截图或数据。
