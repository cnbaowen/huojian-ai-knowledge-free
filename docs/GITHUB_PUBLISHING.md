# GitHub 发布操作手册

本文件给出从本地发布候选到公开仓库的完整操作顺序。执行前先完成 [发布前人工确认清单](RELEASE_CHECKLIST.md)。

## 1. 建议仓库信息

- 仓库名：`huojian-ai-knowledge-free`
- Description：`可独立部署的企业知识库：文档解析切片、内部知识问答、引用溯源、质量评测与企业微信群机器人。`
- Website：填写正式产品官网；没有正式地址时留空，不要填临时测试地址。
- Topics：`knowledge-base`、`rag`、`enterprise-ai`、`internal-qa`、`vue3`、`laravel`、`docker-compose`、`wecom`、`chinese`
- 默认分支：`main`

## 2. 创建空仓库

在 GitHub 创建 Public 仓库时，不要勾选自动生成 README、`.gitignore` 或 License，本地仓库已经包含这些文件。

## 3. 最后检查

```powershell
git status --short
git log --oneline --decorate -5
git tag --list
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\container-acceptance.ps1
```

Linux/macOS：

```bash
bash scripts/release-check.sh
bash scripts/acceptance-linux.sh
```

确认工作区干净、测试通过、`.env` 不在版本控制中，且仓库内没有真实客户数据或密钥。

## 4. 绑定并推送远程

将 `<OWNER>` 替换为实际 GitHub 账号或组织：

```bash
git remote add origin https://github.com/<OWNER>/huojian-ai-knowledge-free.git
git push -u origin main
git push origin v0.3.0-rc.2
```

不要把商业版仓库设置成该远程地址，也不要从商业版工作区执行这些命令。

## 5. 仓库设置

在 GitHub Settings 中完成：

1. General：关闭不使用的 Wiki；Issues 保持开启。
2. Actions：只允许可信 Actions，工作流默认权限设为只读。
3. Branch protection：保护 `main`，要求 `ci` 通过，禁止强制推送和删除。
4. Security：开启 Private vulnerability reporting、Dependabot alerts 和 secret scanning（仓库计划支持时）。
5. Pull Requests：建议开启自动删除已合并分支。

## 6. 创建 Release

推送 `v0.3.0-rc.2` 标签后，仓库内 `release.yml` 会执行完整检查、生成源码 ZIP 和 SHA256，并创建 Pre-release。Release 正文来自 `docs/releases/v0.3.0-rc.2.md`。

如果不希望自动发布，先在 GitHub Actions 中禁用 `release` 工作流，再手工创建 Pre-release。

## 7. 社交预览与首页

- Social preview 建议使用 `docs/images/dashboard.png`；正式上传前可裁切为 GitHub 推荐的横向比例。
- About 区填写 Description、Website 和 Topics。
- 置顶 README 中的工作台、知识库和内部问答截图。
- 发布后用仓库实际地址替换对外宣传文案中的 `<REPOSITORY_URL>`。

## 8. 发布后检查

- 未登录访问 README、LICENSE、SECURITY 和 Release 页面。
- 从全新目录执行 `git clone`，按 README 启动。
- 检查 Actions 三个任务均为绿色。
- 确认 Issue Forms、Pull Request 模板和 Dependabot 配置生效。
- 确认 Release ZIP 的 SHA256 与附件中的校验文件一致。
