# 发布前人工确认清单

技术检查可以自动完成，但以下事项必须由仓库所有者确认，不能由构建器代填。

## 必须确认

- [ ] GitHub 所有者账号或组织名称已确定。
- [ ] 仓库最终名称和公开网址已确定。
- [ ] `Copyright 2026 Huojian AI contributors` 的权利主体表述已获确认。
- [ ] `火建AI`、`Huojian AI` 名称和 Logo 可以用于该公开仓库。
- [ ] 已确定私密安全报告渠道，并开启 GitHub Private vulnerability reporting。
- [ ] README 中的功能、截图和免费版边界符合最终公开口径。
- [ ] 仓库内演示数据均为虚构内容，没有真实客户、员工或业务秘密。
- [ ] 未配置商业版数据库、网络、容器、模型密钥或客户 Webhook。

## 建议确认

- [ ] 产品官网或联系页面已确定。
- [ ] 是否开启 GitHub Discussions。
- [ ] 社区问题的维护人员和处理节奏已确定。
- [ ] 商业咨询入口与社区技术支持边界已确定。
- [ ] `v0.3.0-rc.2` 作为 Pre-release 发布，而不是 Stable release。

全部确认后，可以将 `BUILD_PROVENANCE.json` 中的 `public_ready` 改为 `true`，重新运行边界检查并创建最终标签。当前保持 `false` 是有意的安全门禁。
