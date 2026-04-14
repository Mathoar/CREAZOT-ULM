path = "/root/CREAZOT-ULM/pwa/components/admin/layout/Menu.tsx"
content = open(path).read()

old = """                to={`/clients/${client.id}`}"""
new = """                to={`/clients/${encodeURIComponent(client['@id'] || '/clients/' + client.id)}`}"""

if old in content:
    content = content.replace(old, new)
    print("Menu URL fixed OK")
else:
    print("Pattern not found")

open(path, "w").write(content)
