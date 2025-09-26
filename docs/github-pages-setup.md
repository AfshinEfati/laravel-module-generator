# GitHub Pages Deployment Settings

This project does not configure GitHub Pages automatically. Follow these steps in the GitHub repository settings to publish the documentation or demo site manually.

## 1. Configure the publishing source
1. Open the repository on GitHub and select **Settings**.
2. In the sidebar, choose **Pages**.
3. Under **Build and deployment**, pick the preferred publishing source:
   - **Deploy from a branch** if you want GitHub to serve static files directly from a branch.
   - **GitHub Actions** if you have a workflow that builds the site before deploying.

## 2. Prepare the `gh-pages` branch (branch deployments)
If you opt to deploy from a branch and want to keep the Pages content separate from your main branch:
1. Create the `gh-pages` branch locally or on GitHub.
2. Push any static site artifacts to the `gh-pages` branch.
3. Return to **Settings → Pages** and select `gh-pages` as the branch.
4. Review the **Required deployments** section to ensure there are no blocking checks that must pass before Pages can deploy.

## 3. Domain and HTTPS settings
1. (Optional) Provide a custom domain under **Custom domain**. GitHub will start DNS verification immediately.
2. Enable **Enforce HTTPS** so visitors are redirected to the secure version once the TLS certificate is issued.

After saving your changes, GitHub Pages will deploy automatically. Monitor the deployment status banner at the top of the **Pages** screen for confirmation or troubleshooting details.
