# 安装说明

## 推荐：Docker Compose

要求：Docker Engine 24+，Docker Compose v2+，至少 2 核 CPU、4 GB 内存和 5 GB 可用磁盘。

```bash
cp .env.example .env
```

必须修改 `.env` 中的 `APP_KEY`、`DB_PASSWORD`、数据库 root 密码和 `FREE_API_TOKEN`。生成 Laravel 应用密钥：

```bash
docker run --rm php:8.3-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)), PHP_EOL;"
```

然后启动并迁移：

```bash
docker compose up -d --build
docker compose exec backend php artisan migrate --force
```

访问前端 `http://localhost:18080`，登录时输入 `FREE_API_TOKEN`。

停止服务：

```bash
docker compose down
```

`docker compose down -v` 会删除免费版数据库和上传文档，只能在确定不再需要数据时执行。

## 本地开发

要求 PHP 8.2+、Composer 2、Node.js 22+、npm 10+。本地解析 DOCX/XLSX 需要 PHP Zip 扩展，PDF 需要 `pdftotext`；Docker 镜像已包含这些依赖。

```bash
composer --working-dir=backend install
npm --prefix frontend ci
cp .env.example .env
```

配置 SQLite 时先创建空文件，并把 `.env` 中 `DB_CONNECTION` 改为 `sqlite`、`DB_DATABASE` 改为绝对路径。随后运行迁移和开发服务。

## 验收

Windows：

```powershell
powershell.exe -NoProfile -ExecutionPolicy Bypass -File .\scripts\acceptance.ps1
```

Linux/macOS：

```bash
bash scripts/acceptance-linux.sh
```
