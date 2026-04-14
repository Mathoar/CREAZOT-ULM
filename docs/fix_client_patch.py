path = "/root/CREAZOT-ULM/api/src/Entity/Client.php"
content = open(path).read()

# Add Patch import
if "use ApiPlatform\\Metadata\\Patch;" not in content:
    content = content.replace(
        "use ApiPlatform\\Metadata\\Put;",
        "use ApiPlatform\\Metadata\\Patch;\nuse ApiPlatform\\Metadata\\Put;"
    )
    print("1. Patch import added OK")

# Add Patch operation after Put
old_put = """        new Put(
            uriTemplate: '/clients/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),"""

new_put = """        new Put(
            uriTemplate: '/clients/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Patch(
            uriTemplate: '/clients/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),"""

if "new Patch(" not in content:
    content = content.replace(old_put, new_put)
    print("2. Patch operation added OK")
else:
    print("2. Patch operation already present")

open(path, "w").write(content)
print("Done!")
