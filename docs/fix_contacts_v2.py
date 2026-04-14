path = "/root/CREAZOT-ULM/pwa/app/lib/client.js"
content = open(path).read()

old = """    delete sanitized.airports;
    delete sanitized.cameras;"""

new = """    delete sanitized.airports;
    delete sanitized.cameras;
    delete sanitized.contacts;"""

if old in content:
    content = content.replace(old, new)
    print("sanitizeData: contacts excluded OK")
else:
    print("sanitizeData pattern not found")

open(path, "w").write(content)
print("Done!")
