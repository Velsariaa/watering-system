import sys
import json
import cv2
import os

def process_image(image_path):
    try:
        # Validate the image file path
        if not os.path.exists(image_path):
            return {"error": "Image file not found"}

        # Load the image
        image = cv2.imread(image_path)
        if image is None:
            return {"error": "Failed to load image"}

        # Convert the image to grayscale
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

        # Apply Gaussian blur to reduce noise
        blurred = cv2.GaussianBlur(gray, (5, 5), 0)

        # Use binary thresholding to separate plant from background
        _, binary = cv2.threshold(blurred, 60, 255, cv2.THRESH_BINARY_INV)

        # Find contours in the binary image
        contours, _ = cv2.findContours(binary, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)

        if not contours:
            return {"error": "No plant detected in the image"}

        # Assume the largest contour is the plant
        largest_contour = max(contours, key=cv2.contourArea)

        # Calculate the bounding box of the plant
        x, y, w, h = cv2.boundingRect(largest_contour)

        # Define a pixel-to-cm ratio (adjust as needed)
        pixel_to_cm_ratio = 10.0  # Example: 10 pixels = 1 cm

        # Convert dimensions to cm
        plant_width_cm = w / pixel_to_cm_ratio
        plant_height_cm = h / pixel_to_cm_ratio

        # Save a debug image with bounding box
        debug_image = image.copy()
        cv2.rectangle(debug_image, (x, y), (x + w, y + h), (0, 255, 0), 2)
        debug_path = image_path.replace(".jpg", "_debug.jpg").replace(".png", "_debug.png")
        cv2.imwrite(debug_path, debug_image)

        # Return results
        return {
            "width": round(plant_width_cm, 2),
            "height": round(plant_height_cm, 2),
            "debug_image": debug_path
        }

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) != 2:
        print(json.dumps({"error": "Invalid arguments"}))
        sys.exit(1)

    image_path = sys.argv[1]
    result = process_image(image_path)
    print(json.dumps(result))
