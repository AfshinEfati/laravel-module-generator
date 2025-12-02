# Laravel Module Generator - Documentation Site

Documentation website for the Laravel Module Generator package.

## 🚀 Quick Start

```bash
# Install dependencies
npm install

# Development server
npm run dev

# Build for production
npm run build

# Generate static site
npm run generate

# Preview production build
npm run preview
```

## 📁 Project Structure

```
docs-site/
├── assets/          # CSS and fonts
├── components/      # Vue components (ProseCode, ProsePre)
├── content/         # Markdown documentation
│   ├── en/         # English docs
│   └── fa/         # Persian docs
├── layouts/         # Page layouts
├── pages/          # Route pages
├── public/         # Static assets (favicon, og-image)
└── nuxt.config.ts  # Nuxt configuration
```

## 🎨 Creating OG Image

See [HOW_TO_GENERATE_OG_IMAGE.md](./HOW_TO_GENERATE_OG_IMAGE.md) for instructions.

**Quick method:**
1. Open `og-image-template.html` in browser
2. Click "Download OG Image" button
3. Save as `public/og-image.png`

## 🌐 Deployment

This site is configured for GitHub Pages deployment:

```bash
npm run generate
# Output will be in .output/public/
```

The site is deployed at: `https://afshinefati.github.io/Laravel-Scaffolder/`

## 📝 Adding Content

### English Documentation
Add/edit files in `content/en/`

### Persian Documentation
Add/edit files in `content/fa/`

### Code Blocks
Code blocks automatically include:
- Language label
- Copy button
- Syntax highlighting

## 🔧 Technologies

- **Nuxt 3** - Vue.js framework
- **Nuxt Content** - File-based CMS
- **TailwindCSS** - Utility-first CSS
- **Typography Plugin** - Beautiful prose styling

## 📦 No Puppeteer Required

Previous versions used Puppeteer for OG image generation, but it's no longer needed. Use the HTML template with the download button instead.

## 🤝 Contributing

1. Edit content in `content/en/` or `content/fa/`
2. Test locally with `npm run dev`
3. Build with `npm run generate`
4. Deploy to GitHub Pages

## 📄 License

Same as the main Laravel Module Generator package.
