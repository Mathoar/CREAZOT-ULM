import re

# --- 1. Dashboard.jsx : conditionner le bouton Caméras + arrondis ---
path1 = "/root/CREAZOT-ULM/pwa/components/dashboard/components/Dashboard/Dashboard.jsx"
c1 = open(path1).read()

# Replace the 3 buttons block with conditional Caméras
old_buttons = """              <button 
                className={`bg-white hover:bg-gray-100 active:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 ${appli === "M" && 'active'}`}
                onClick={ onMSelect }
              >
                <span className="hidden sm:inline">Météo&Radar</span>
                <span className="inline sm:hidden">M&Radar</span>
              </button>
              <button 
                className={`bg-white hover:bg-gray-100 active:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded-r ${appli === "C" && 'active'}`}
                onClick={ onCSelect }
              >
                <span className="hidden sm:inline">Caméras</span>
                <span className="inline sm:hidden">Cams</span>
              </button>"""

new_buttons = """              <button 
                className={`bg-white hover:bg-gray-100 active:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 ${!client.hasCams ? 'rounded-r' : ''} ${appli === "M" && 'active'}`}
                onClick={ onMSelect }
              >
                <span className="hidden sm:inline">Météo&Radar</span>
                <span className="inline sm:hidden">M&Radar</span>
              </button>
              { client.hasCams &&
              <button 
                className={`bg-white hover:bg-gray-100 active:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border border-gray-400 rounded-r ${appli === "C" && 'active'}`}
                onClick={ onCSelect }
              >
                <span className="hidden sm:inline">Caméras</span>
                <span className="inline sm:hidden">Cams</span>
              </button>
              }"""

if old_buttons in c1:
    c1 = c1.replace(old_buttons, new_buttons)
    print("1a. Dashboard buttons conditional OK")
else:
    print("1a. Dashboard buttons pattern not found")

# Also wrap the Cameras section
old_cameras = """            <div className={ `camera-container ${ appli === "C" ? "visible" : "invisible no-visible-cam"}`}>
              <Cameras client={ client }/>
            </div>"""

new_cameras = """            { client.hasCams &&
            <div className={ `camera-container ${ appli === "C" ? "visible" : "invisible no-visible-cam"}`}>
              <Cameras client={ client }/>
            </div>
            }"""

if old_cameras in c1:
    c1 = c1.replace(old_cameras, new_cameras)
    print("1b. Dashboard Cameras section conditional OK")
else:
    print("1b. Dashboard Cameras section pattern not found")

open(path1, "w").write(c1)

# --- 2. ModulePacksCreate.tsx : ajouter hasCams ---
path2 = "/root/CREAZOT-ULM/pwa/components/admin/modulePack/ModulePacksCreate.tsx"
c2 = open(path2).read()

old_create = '  { id: "hasVoiceAssistant", name: "Assistant vocal (téléphone)" },\n];'
new_create = '  { id: "hasVoiceAssistant", name: "Assistant vocal (téléphone)" },\n  { id: "hasCams", name: "Caméras Windy" },\n];'

if old_create in c2:
    c2 = c2.replace(old_create, new_create)
    print("2. ModulePacksCreate hasCams added OK")
else:
    print("2. ModulePacksCreate pattern not found")

open(path2, "w").write(c2)

# --- 3. ModulePacksEdit.tsx : ajouter hasCams ---
path3 = "/root/CREAZOT-ULM/pwa/components/admin/modulePack/ModulePacksEdit.tsx"
c3 = open(path3).read()

old_edit = '  { id: "hasVoiceAssistant", name: "Assistant vocal (téléphone)" },\n];'
new_edit = '  { id: "hasVoiceAssistant", name: "Assistant vocal (téléphone)" },\n  { id: "hasCams", name: "Caméras Windy" },\n];'

if old_edit in c3:
    c3 = c3.replace(old_edit, new_edit)
    print("3. ModulePacksEdit hasCams added OK")
else:
    print("3. ModulePacksEdit pattern not found")

open(path3, "w").write(c3)

print("Done!")
