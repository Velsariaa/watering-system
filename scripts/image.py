import cv2
import numpy as np
import mysql.connector

# Database connection
conn = mysql.connector.connect(
    host="localhost",
    user="root",
    password="",
    database="new_user"
)
cursor = conn.cursor()

# Fetch images without dimensions
cursor.execute("SELECT id, image FROM plant_images WHERE height IS NULL OR width IS NULL")
rows = cursor.fetchall()

for row in rows:
    image_id, image_data = row
    nparr = np.frombuffer(image_data, np.uint8)
    img = cv2.imdecode(nparr, cv2.IMREAD_COLOR)

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    _, thresh = cv2.threshold(gray, 150, 255, cv2.THRESH_BINARY_INV)

    contours, _ = cv2.findContours(thresh, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    largest_contour = max(contours, key=cv2.contourArea)

    x, y, w, h = cv2.boundingRect(largest_contour)

    cursor.execute("UPDATE plant_images SET height = %s, width = %s WHERE id = %s", (h, w, image_id))
    conn.commit()

conn.close()
