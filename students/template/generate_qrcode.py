import qrcode
import sys

# Get client details and file path from command line arguments
client_details = sys.argv[1]
file_path = sys.argv[2]

# Create the QR code
qr = qrcode.QRCode(
    version=1,
    error_correction=qrcode.constants.ERROR_CORRECT_L,
    box_size=10,
    border=4,
)
qr.add_data(client_details)
qr.make(fit=True)

# Create the image
img = qr.make_image(fill='black', back_color='white')

# Save the image as a .png file
img.save(file_path, "PNG")
