path = "/root/CREAZOT-ULM/pwa/components/admin/prestation/Form/PlusForm.jsx"
content = open(path).read()

# Replace getContacts to use client.contacts instead of fetching all
old = """  const getContacts = () => {
      dataProvider
        .getList('contacts', {})
        .then(({ data }) => setContacts(data.map(d => ({...d, value: d['@id']}))));
  };"""

new = """  const getContacts = () => {
      const clientContacts = client?.contacts ?? [];
      setContacts(clientContacts.map(d => ({...d, value: d['@id']})));
  };"""

if old in content:
    content = content.replace(old, new)
    print("PlusForm contacts filter OK")
else:
    print("PlusForm pattern not found")

open(path, "w").write(content)
print("Done!")
