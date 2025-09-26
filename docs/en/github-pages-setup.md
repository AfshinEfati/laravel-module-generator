# GitHub Pages setup

[🇮🇷 فارسی](../fa/github-pages-setup.md){ .language-switcher }


The repository already ships with a GitHub Actions workflow that builds and publishes the MkDocs site to GitHub Pages. Follow the steps below to keep deployments healthy.

## 1. Configure repository settings

1. Open **Settings → Pages** in GitHub.
2. Set the deployment source to **GitHub Actions**.
3. Ensure the custom domain (if any) matches the value configured in `site_url` inside `mkdocs.yml`.

## 2. Understand the workflow

The workflow file lives at `.github/workflows/docs.yml` and performs the following steps:

1. Check out the repository.
2. Install Python and MkDocs Material.
3. Run `mkdocs build --strict` to validate the documentation.
4. Upload the generated `site/` directory as an artifact.
5. Deploy the artifact to GitHub Pages.

If a step fails the deployment is cancelled to prevent inconsistent states.

## 3. Preview locally before pushing

Use the MkDocs development server to preview both language variants:

```bash
mkdocs serve
```

Visit `http://127.0.0.1:8000/en/` and `http://127.0.0.1:8000/fa/` to verify the layout, RTL styling, and language switcher.

## 4. Add branch protections (optional)

- Require the “Docs (MkDocs → GitHub Pages)” workflow to pass before allowing merges into `main`.
- Enable required reviews so documentation changes are checked by teammates.

Keeping the workflow green ensures every change to the documentation is automatically published with the latest code.
