import re

# --- ReservationsCreate.tsx ---
path1 = "/root/CREAZOT-ULM/pwa/components/admin/reservation/ReservationsCreate.tsx"
c1 = open(path1).read()

# Add SelectArrayInput import
if "SelectArrayInput" not in c1:
    c1 = c1.replace("AutocompleteInput}", "AutocompleteInput, SelectArrayInput}")
    print("Create: SelectArrayInput import added")

# Replace OriginContactInput
old_create = """const OriginContactInput = ({ client }) => !clientWithOriginContact(client) ? null : 
  <ReferenceArrayInput source="contact" reference="contacts" label="Contact initial"/>"""

new_create = """const OriginContactInput = ({ client }) => {
  if (!clientWithOriginContact(client)) return null;
  const choices = (client?.contacts ?? []).map(c => ({ id: c['@id'], name: c.name }));
  return <SelectArrayInput source="contact" choices={choices} label="Canal de contact"/>;
}"""

if old_create in c1:
    c1 = c1.replace(old_create, new_create)
    print("Create: OriginContactInput replaced OK")
else:
    print("Create: OriginContactInput pattern not found")

open(path1, "w").write(c1)

# --- ReservationsEdit.tsx ---
path2 = "/root/CREAZOT-ULM/pwa/components/admin/reservation/ReservationsEdit.tsx"
c2 = open(path2).read()

old_edit = """    return <ReferenceArrayInput source="contacts" reference="contacts" label="Origine"/>;
}"""

new_edit = """    const choices = (client?.contacts ?? []).map(c => ({ id: c['@id'], name: c.name }));
    return <SelectArrayInput source="contacts" choices={choices} label="Canal de contact"/>;
}"""

if old_edit in c2:
    c2 = c2.replace(old_edit, new_edit)
    print("Edit: OriginContactInput replaced OK")
else:
    print("Edit: OriginContactInput pattern not found")

open(path2, "w").write(c2)
print("Done!")
