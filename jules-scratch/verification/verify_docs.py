from playwright.sync_api import Page, expect
import traceback

def test_docs_navigation(page: Page):
    """
    This test verifies that the new "Features" section and its pages
    are correctly added to the documentation site.
    """
    try:
        print("Starting verification script...")
        # 1. Arrange: Go to the documentation homepage.
        page.goto("http://localhost:3000/en")
        print("Navigated to homepage.")

        # 2. Act: Find the "Features" section and click on the "Generating Modules" link.
        features_link = page.get_by_role("link", name="Generating Modules")
        features_link.click()
        print("Clicked on 'Generating Modules' link.")

        # 3. Assert: Confirm the navigation was successful.
        expect(page).to_have_title("Generating Modules · Laravel Module Generator")
        expect(page.get_by_role("heading", name="Generating Modules")).to_be_visible()
        print("Assertions passed.")

        # 4. Screenshot: Capture the final result for visual verification.
        screenshot_path = "jules-scratch/verification/docs-screenshot.png"
        page.screenshot(path=screenshot_path)
        print(f"Screenshot saved to {screenshot_path}")

        page.pause()

    except Exception as e:
        print(f"An error occurred: {e}")
        traceback.print_exc()