import whisper
import sys

model = whisper.load_model("base")  # modèle léger

audio_path = sys.argv[1]

result = model.transcribe(audio_path)

print(result["text"])