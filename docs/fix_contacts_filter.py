import re

# --- 1. PlusForm.jsx: use filter { client: client.id } ---
path1 = "/root/CREAZOT-ULM/pwa/components/admin/prestation/Form/PlusForm.jsx"
c1 = open(path1).read()

old_pf = """  const getContacts = () => {
      const clientContacts = client?.contacts ?? [];
      setContacts(clientContacts.map(d => ({...d, value: d['@id']})));
  };"""

new_pf = """  const getContacts = () => {
      dataProvider
        .getList('contacts', { filter: { client: client?.id }, sort: { field: 'name', order: 'ASC' }, pagination: { page: 1, perPage: 100 } })
        .then(({ data }) => setContacts(data.map(d => ({...d, value: d['@id']}))));
  };"""

if old_pf in c1:
    c1 = c1.replace(old_pf, new_pf)
    print("1. PlusForm fixed OK")
else:
    print("1. PlusForm pattern not found — checking alternative...")
    # Try the original pattern
    old_pf2 = """  const getContacts = () => {
      dataProvider
        .getList('contacts', {})
        .then(({ data }) => setContacts(data.map(d => ({...d, value: d['@id']}))));
  };"""
    if old_pf2 in c1:
        c1 = c1.replace(old_pf2, new_pf)
        print("1. PlusForm fixed (original pattern) OK")
    else:
        print("1. PlusForm: NO pattern found!")

open(path1, "w").write(c1)

# --- 2. ReservationsCreate.tsx: back to ReferenceArrayInput with filter ---
path2 = "/root/CREAZOT-ULM/pwa/components/admin/reservation/ReservationsCreate.tsx"
c2 = open(path2).read()

# Check what's currently there
old_create = """const OriginContactInput = ({ client }) => {
  if (!clientWithOriginContact(client)) return null;
  const choices = (client?.contacts ?? []).map(c => ({ id: c['@id'], name: c.name }));
  return <SelectArrayInput source="contact" choices={choices} label="Canal de contact"/>;
}"""

new_create = """const OriginContactInput = ({ client }) => !clientWithOriginContact(client) ? null : 
  <ReferenceArrayInput source="contact" reference="contacts" label="Canal de contact" filter={{ client: client?.id }}/>"""

if old_create in c2:
    c2 = c2.replace(old_create, new_create)
    print("2. ReservationsCreate fixed OK")
else:
    print("2. ReservationsCreate pattern not found")

# Remove SelectArrayInput import if no longer needed
if "SelectArrayInput" in c2 and "SelectArrayInput" not in c2.replace("SelectArrayInput}", ""):
    c2 = c2.replace(", SelectArrayInput}", "}")
    print("2b. Removed SelectArrayInput import")

open(path2, "w").write(c2)

# --- 3. ReservationsEdit.tsx: back to ReferenceArrayInput with filter ---
path3 = "/root/CREAZOT-ULM/pwa/components/admin/reservation/ReservationsEdit.tsx"
c3 = open(path3).read()

old_edit = """    const choices = (client?.contacts ?? []).map(c => ({ id: c['@id'], name: c.name }));
    return <SelectArrayInput source="contacts" choices={choices} label="Canal de contact"/>;"""

new_edit = """    return <ReferenceArrayInput source="contacts" reference="contacts" label="Canal de contact" filter={{ client: client?.id }}/>;"""

if old_edit in c3:
    c3 = c3.replace(old_edit, new_edit)
    print("3. ReservationsEdit fixed OK")
else:
    print("3. ReservationsEdit pattern not found")

open(path3, "w").write(c3)

print("Done!")
