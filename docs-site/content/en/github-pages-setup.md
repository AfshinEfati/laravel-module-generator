# GitHub Pages setup

[🇮🇷 فارسی](../fa/github-pages-setup.md){ .language-switcher }


The repository ships with a GitHub Actions workflow that builds the Nuxt-powered documentation and publishes it to GitHub Pages. Follow the steps below to keep deployments healthy.

## 1. Configure repository settings

1. Open **Settings → Pages** in GitHub.
2. Set the deployment source to **GitHub Actions**.
3. Ensure the custom domain (if any) matches the value configured in your Pages settings.

## 2. Understand the workflow

The workflow file lives at `.github/workflows/docs.yml` and performs the following steps:

1. Check out the repository.
2. Set up Node.js 18.
3. Run `npm install` inside `docs-site/`.
4. Generate the static documentation with `npm run generate` (Nuxt prerender).
5. Upload the generated `.output/public` directory as an artifact.
6. Deploy the artifact to GitHub Pages.

If a step fails the deployment is cancelled to prevent inconsistent states.

## 3. Preview locally before pushing

Boot the Nuxt development server to preview both language variants:

```bash
cd docs-site
npm install
npm run dev
```
- Visit `http://localhost:3000/en/index` and `http://localhost:3000/fa/index` to verify the layout, RTL styling, and language switcher.

## 4. Add branch protections (optional)

- Require the “Docs (Nuxt → GitHub Pages)” workflow to pass before allowing merges into `main`.
- Enable required reviews so documentation changes are checked by teammates.

Keeping the workflow green ensures every change to the documentation is automatically published with the latest code.
