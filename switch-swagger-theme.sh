#!/bin/bash

# Swagger UI Theme Switcher
# استفاده: ./switch-swagger-theme.sh [vanilla|tailwind|tailwind-dark]

THEME=${1:-vanilla}
SWAGGER_UI_DIR="storage/swagger-ui"

if [ ! -d "$SWAGGER_UI_DIR" ]; then
    echo "❌ Error: $SWAGGER_UI_DIR directory not found"
    echo "💡 Run: php artisan swagger:init"
    exit 1
fi

# Backup current
cp "$SWAGGER_UI_DIR/index.html" "$SWAGGER_UI_DIR/index.html.backup" 2>/dev/null

case $THEME in
    vanilla)
        if [ -f "src/Stubs/SwaggerUI/index.html" ]; then
            cp "src/Stubs/SwaggerUI/index.html" "$SWAGGER_UI_DIR/index.html"
            echo "✅ Theme switched to: Vanilla CSS (no dependencies)"
            echo "📍 Theme: $SWAGGER_UI_DIR/index.html"
        else
            echo "❌ Vanilla theme not found in src/Stubs/SwaggerUI/"
        fi
        ;;

    tailwind)
        if [ -f "src/Stubs/SwaggerUI/tailwind-index.html" ]; then
            cp "src/Stubs/SwaggerUI/tailwind-index.html" "$SWAGGER_UI_DIR/index.html"
            echo "✅ Theme switched to: Tailwind CSS"
            echo "📍 Theme: $SWAGGER_UI_DIR/index.html"
            echo "ℹ️  Uses Tailwind CDN - fully customizable"
        else
            echo "❌ Tailwind theme not found in src/Stubs/SwaggerUI/"
        fi
        ;;

    tailwind-dark|dark)
        if [ -f "src/Stubs/SwaggerUI/dark-mode-index.html" ]; then
            cp "src/Stubs/SwaggerUI/dark-mode-index.html" "$SWAGGER_UI_DIR/index.html"
            echo "✅ Theme switched to: Tailwind with Dark Mode"
            echo "📍 Theme: $SWAGGER_UI_DIR/index.html"
            echo "ℹ️  Includes auto dark mode toggle"
        else
            echo "❌ Dark mode theme not found in src/Stubs/SwaggerUI/"
        fi
        ;;

    *)
        echo "❌ Unknown theme: $THEME"
        echo ""
        echo "📋 Available themes:"
        echo "  • vanilla      - Pure CSS (default, no dependencies)"
        echo "  • tailwind     - Tailwind CSS with Alpine.js"
        echo "  • dark         - Tailwind with dark mode support"
        echo ""
        echo "💡 Usage:"
        echo "  ./switch-swagger-theme.sh vanilla"
        echo "  ./switch-swagger-theme.sh tailwind"
        echo "  ./switch-swagger-theme.sh dark"
        exit 1
        ;;
esac

echo ""
echo "🚀 Restart server with:"
echo "   php artisan swagger:ui"
