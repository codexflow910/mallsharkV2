from flask import Flask, request, jsonify
from PIL import Image
from transformers import BlipProcessor, BlipForConditionalGeneration
import torch
import re
import warnings
import os

# setup on new server
# pip install torch torchvision pillow transformers flask
# sudo apt update
# sudo apt install python3 python3-pip -y
# use to run locally 'python3 public/image_tagger_server.py'
# use to run on server 'nohup python3 public/image_tagger_server.py > /dev/null 2>&1 &'
warnings.filterwarnings("ignore")
os.environ["TRANSFORMERS_NO_ADVISORY_WARNINGS"] = "1"

app = Flask(__name__)

print("Loading model...")
processor = BlipProcessor.from_pretrained("Salesforce/blip-image-captioning-base")
model = BlipForConditionalGeneration.from_pretrained("Salesforce/blip-image-captioning-base")
model.eval()  # Optimization: set to evaluation mode
print("Model loaded and ready!")

@app.route('/tag', methods=['POST'])
def tag_image():
    if 'image' not in request.files:
        return jsonify({'error': 'No image provided'}), 400
    
    try:
        image_file = request.files['image']
        image = Image.open(image_file.stream).convert("RGB")
        
        # Generate caption with optimization
        with torch.no_grad():  # Optimization: disable gradients
            inputs = processor(image, return_tensors="pt")
            out = model.generate(**inputs)
            caption = processor.decode(out[0], skip_special_tokens=True)
        
        # Extract keywords
        words = re.findall(r'\w+', caption.lower())
        
        stopwords = {
            # Articles, prepositions, conjunctions
            'a', 'an', 'the', 'on', 'of', 'and', 'in', 'with', 'for', 'is', 'to', 'at', 
            'by', 'from', 'as', 'that', 'this', 'it', 'are', 'be', 'was', 'were', 's',
            
            # Quantity/grouping words
            'group', 'set', 'piece', 'lot', 'collection', 'bunch', 'stack' ,'variety','varities','display','best','assortment','assortments','bundle','bundles','pack','packs','pairs',
            
            # Action/position words
            'sitting', 'standing', 'lying', 'placed', 'hanging', 'displayed', 'holding',
            'next', 'near', 'under', 'over', 'above', 'below', 'background', 'foreground',
            'top', 'bottom', 'left', 'right', 'center', 'middle', 'running', 'walking', 'jumping', 'flying', 'swimming',
            
            # Non-color descriptive words (size, quality, etc.)
            'big', 'small', 'large', 'tiny', 'huge', 'little',
            'old', 'new', 'young', 'beautiful', 'nice', 'pretty', 'good', 'bad',
            # Colors to KEEP (not remove)
            'red', 'blue', 'green', 'yellow', 'black', 'white', 'brown', 'pink', 
            'orange', 'purple', 'gray', 'grey', 'silver', 'gold', 'beige', 'tan',
            'navy', 'maroon', 'violet', 'indigo', 'cyan', 'magenta', 'lime',
            'dark', 'light', 'bright', 'pale'
        }
        
        keywords = [w for w in words if w not in stopwords]
        
        # Get only first two keywords
        first_two_keywords = keywords[:2] if len(keywords) >= 2 else keywords
        keyword = ' '.join(first_two_keywords) if first_two_keywords else "unknown"
        
        return jsonify({
            'keyword': keyword,
            'caption': caption
        })
    
    except Exception as e:
        return jsonify({'error': str(e)}), 500



if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=False)