import sys
import whisper
import json

if len(sys.argv) < 2:
    print(json.dumps({"error": "No file provided"}))
    sys.exit(1)

audio_path = sys.argv[1]

model = whisper.load_model("base")  # tiny, base, small, medium, large

try:
    result = model.transcribe(audio_path)
    print(json.dumps({"text": result["text"]}))
except Exception as e:
    print(json.dumps({"error": str(e)}))