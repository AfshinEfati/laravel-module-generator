# Open Graph Image Guide

## Current Status
Currently using `og-image.svg` as a placeholder for social media previews.

## Recommended: Create a PNG Image

For better compatibility with all social media platforms, create a PNG image with these specifications:

### Image Specifications
- **Dimensions**: 1200 × 630 pixels
- **Format**: PNG or JPG
- **File size**: Under 1MB (ideally under 300KB)
- **Aspect ratio**: 1.91:1

### Design Recommendations
1. **Background**: Use a gradient or solid color matching your brand
2. **Logo**: Include Laravel Module Generator logo/icon
3. **Title**: "Laravel Module Generator" in large, bold text
4. **Subtitle**: Brief description of the package
5. **URL**: Optional footer with the website URL

### Tools to Create OG Image

#### Online Tools (Easy)
- [Canva](https://www.canva.com/) - Use "Social Media" template (1200×630)
- [Figma](https://www.figma.com/) - Professional design tool
- [OG Image Generator](https://og-image.vercel.app/) - Quick text-based generator

#### Convert SVG to PNG
```bash
# Using ImageMagick
convert -density 300 og-image.svg -resize 1200x630 og-image.png

# Using Inkscape
inkscape og-image.svg --export-filename=og-image.png --export-width=1200 --export-height=630

# Using online converter
# Visit: https://cloudconvert.com/svg-to-png
```

### After Creating the Image
1. Save as `og-image.png` in the `public/` directory
2. Update `nuxt.config.ts` to use `.png` instead of `.svg`
3. Test with:
   - [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
   - [Twitter Card Validator](https://cards-dev.twitter.com/validator)
   - [LinkedIn Post Inspector](https://www.linkedin.com/post-inspector/)

### Testing
After deployment, test your OG tags:
```
https://afshinefati.github.io/laravel-module-generator/
```

The preview should show:
- Title: "Laravel Module Generator - Build Modular Laravel Applications"
- Description: "A powerful Laravel package for generating modular application structures..."
- Image: Your custom OG image
