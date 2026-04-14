#!/usr/bin/env python3
"""
Générateur de SQL de migration Planetair-Gestion → Logic'Ciel SaaS
Lit le dump PostgreSQL source et génère un script SQL d'import pour Logic'Ciel.
"""
import re
import sys
from pathlib import Path

DUMP_FILE = Path(__file__).parent / "planetair_dump_20260409.sql"
OUTPUT_FILE = Path(__file__).parent / "migration_planetair_to_logicciel.sql"

ID_OFFSET = 10000
CLIENT_ID = 5

# UUID mapping: source → target (users existant dans les deux bases)
USER_UUID_MAP = {
    "1effe8dc-f73d-600a-bd2c-3324ddd39a2e": "1f105d7c-e69f-68fe-bfda-c752fddd30bf",  # m_seb@icloud.com
}

SKIP_USER_EMAILS = {"m_seb@icloud.com"}

# profil_pilote ID mapping: source ID → target ID (existing profil_pilote to preserve)
PROFIL_PILOTE_ID_MAP = {
    "1": "1",   # m_seb@icloud.com → profil_pilote id=1 already exists in Logic'Ciel
}
# Source profil_pilote pilote_id (UUID) to skip during INSERT
SKIP_PROFIL_PILOTE_UUIDS = {
    "1f105d7c-e69f-68fe-bfda-c752fddd30bf",  # m_seb@icloud.com (target UUID)
}

# Tables à ignorer complètement
SKIP_TABLES = {"nature", "qualification", "client", "doctrine_migration_versions"}

# Tables avec offset ID entier
OFFSET_TABLES = {
    "aeronef", "airport", "cadeau", "camera", "carnet_vol", "certificat_medical",
    "circuit", "combinaison", "contact", "disponibilite", "entretien", "expense",
    "landing", "media_object", "option", "origine", "passager", "payment",
    "payment_detail", "pilot_qualification", "prestation", "profil_pilote",
    "rappel", "reservation", "vol",
}

# Tables de jointure (offset sur les FK entières)
JOIN_TABLES = {
    "cadeau_origine", "circuit_qualification", "entretien_user",
    "payment_origine", "profil_pilote_qualification",
    "reservation_contact", "reservation_origine",
}

# Colonnes UUID qui pointent vers user.id → doivent être remappées
UUID_FK_COLUMNS = {
    "pilote_id", "encadrant_id", "created_by_id", "updated_by_id",
}

# Tables qui reçoivent client_id = 5
CLIENT_ID_TABLES = {
    "aeronef", "cadeau", "circuit", "combinaison", "contact", "entretien",
    "expense", "landing", "media_object", "option", "origine", "passager",
    "payment", "payment_detail", "prestation", "rappel", "reservation", "vol",
}

# FK entières à décaler (+10000) par table
FK_INT_MAP = {
    "reservation": {"circuit_id", "option_id", "avion_id", "cadeau_id"},
    "vol": {"circuit_id", "prestation_id", "option_id"},
    "landing": {"vol_id"},
    "prestation": {"aeronef_id"},
    "cadeau": {"circuit_id", "option_id", "options_id"},
    "entretien": {"aeronef_id"},
    "expense": {"document_id", "entretien_id"},
    "payment_detail": {"payment_id", "prepayment_id", "expense_id"},
    "media_object": {"profil_pilote_id", "aeronef_id", "airport_id", "entretien_id"},
    "carnet_vol": {"profil_id", "type_de_vol_id"},
    "pilot_qualification": {"profil_id", "qualification_id", "document_id"},
    "certificat_medical": {"profil_id", "document_id"},
    "disponibilite": {"pilote_id"},
    "profil_pilote": {},
}

# FK entières dans les tables de jointure
JOIN_FK_MAP = {
    "cadeau_origine": {"cadeau_id", "origine_id"},
    "circuit_qualification": {"circuit_id"},
    "entretien_user": {"entretien_id"},
    "payment_origine": {"payment_id", "origine_id"},
    "profil_pilote_qualification": {"profil_pilote_id"},
    "reservation_contact": {"reservation_id", "contact_id"},
    "reservation_origine": {"reservation_id", "origine_id"},
}

# Colonnes qui sont des UUID FK vers user dans les tables de jointure
JOIN_UUID_FK = {
    "entretien_user": {"user_id"},
}

# Airport: source IDs to skip (already exist in target for client 5)
AIRPORT_SKIP_IDS = {"1", "2"}  # FMEP et FMEE already in target as IDs 13, 14
AIRPORT_ID_REMAP = {"1": "13", "2": "14"}  # source ID → target ID for FK references

# Colonnes FK entières qui pointent vers nature.id (mêmes IDs, PAS d'offset)
NO_OFFSET_FK_COLUMNS = {"type_de_vol_id", "nature_id"}

# Camera: source has client_id=1, we change to client_id=5
CAMERA_CLIENT_OVERRIDE = True


def parse_copy_blocks(dump_path: str) -> dict:
    """Parse les blocs COPY ... FROM stdin du dump PostgreSQL."""
    blocks = {}
    current_table = None
    current_cols = None
    current_rows = []

    with open(dump_path, "r", encoding="utf-8") as f:
        for line in f:
            if line.startswith("COPY public."):
                match = re.match(
                    r'COPY public\."?(\w+)"?\s+\((.+?)\)\s+FROM stdin;', line
                )
                if match:
                    current_table = match.group(1)
                    current_cols = [c.strip().strip('"') for c in match.group(2).split(",")]
                    current_rows = []
            elif line.strip() == "\\.":
                if current_table and current_cols:
                    blocks[current_table] = {
                        "columns": current_cols,
                        "rows": current_rows,
                    }
                current_table = None
                current_cols = None
                current_rows = []
            elif current_table is not None:
                current_rows.append(line.rstrip("\n").split("\t"))

    return blocks


def escape_sql(val: str) -> str:
    """Échappe une valeur pour SQL."""
    if val == "\\N":
        return "NULL"
    escaped = val.replace("'", "''").replace("\\n", "\n")
    return f"'{escaped}'"


def map_uuid(val: str) -> str:
    """Remappe un UUID user si nécessaire."""
    if val == "\\N":
        return "NULL"
    mapped = USER_UUID_MAP.get(val, val)
    return f"'{mapped}'"


def offset_int(val: str, remap: dict = None) -> str:
    """Applique l'offset +10000 à un ID entier, avec remapping optionnel."""
    if val == "\\N":
        return "NULL"
    if remap and val in remap:
        return remap[val]
    try:
        return str(int(val) + ID_OFFSET)
    except ValueError:
        return escape_sql(val)


def generate_insert(table: str, columns: list, values_list: list) -> str:
    """Génère un INSERT multi-values."""
    if not values_list:
        return ""
    col_str = ", ".join(f'"{c}"' for c in columns)
    rows_sql = []
    for vals in values_list:
        rows_sql.append(f"  ({', '.join(vals)})")
    return f'INSERT INTO "{table}" ({col_str}) VALUES\n' + ",\n".join(rows_sql) + ";\n"


def process_user_table(block: dict) -> tuple:
    """Traite la table user : sépare les users à insérer des existants."""
    cols = block["columns"]
    email_idx = cols.index("email")
    id_idx = cols.index("id")

    insert_rows = []
    for row in block["rows"]:
        email = row[email_idx]
        if email in SKIP_USER_EMAILS:
            continue
        values = []
        for i, col in enumerate(cols):
            val = row[i]
            if col == "id":
                values.append(map_uuid(val))
            elif col == "roles":
                values.append(escape_sql(val) if val != "\\N" else "'[]'")
            elif col == "keycloak_id":
                values.append("NULL")
            else:
                values.append(escape_sql(val))
        insert_rows.append(values)

    return cols, insert_rows


def process_offset_table(table: str, block: dict) -> tuple:
    """Traite une table avec offset ID et injection client_id."""
    cols = block["columns"]
    fk_ints = FK_INT_MAP.get(table, set())

    out_cols = list(cols)
    if table in CLIENT_ID_TABLES and "client_id" not in out_cols:
        out_cols.append("client_id")

    # FK columns that reference profil_pilote.id (need remapping)
    profil_fk_cols = {"profil_id", "profil_pilote_id"}

    insert_rows = []
    for row in block["rows"]:
        if table == "airport" and row[0] in AIRPORT_SKIP_IDS:
            continue
        if table == "expense" and len(block["rows"]) == 0:
            continue
        # Skip profil_pilote for already-existing users
        if table == "profil_pilote":
            pilote_id_idx = cols.index("pilote_id")
            mapped_uuid = USER_UUID_MAP.get(row[pilote_id_idx], row[pilote_id_idx])
            if mapped_uuid in SKIP_PROFIL_PILOTE_UUIDS:
                continue

        values = []
        for i, col in enumerate(cols):
            val = row[i]
            if col == "id":
                if table == "profil_pilote":
                    values.append(offset_int(val, PROFIL_PILOTE_ID_MAP))
                else:
                    values.append(offset_int(val))
            elif col in fk_ints:
                if col in NO_OFFSET_FK_COLUMNS or col == "qualification_id":
                    values.append("NULL" if val == "\\N" else val)
                elif col == "airport_id" and val in AIRPORT_ID_REMAP:
                    values.append(AIRPORT_ID_REMAP[val])
                elif col in profil_fk_cols:
                    values.append(offset_int(val, PROFIL_PILOTE_ID_MAP))
                else:
                    values.append(offset_int(val))
            elif col in UUID_FK_COLUMNS:
                values.append(map_uuid(val))
            elif col == "client_id":
                values.append(str(CLIENT_ID))
            else:
                values.append(escape_sql(val))

        if table in CLIENT_ID_TABLES and "client_id" not in cols:
            values.append(str(CLIENT_ID))

        insert_rows.append(values)

    return out_cols, insert_rows


def process_join_table(table: str, block: dict) -> tuple:
    """Traite une table de jointure."""
    cols = block["columns"]
    fk_ints = JOIN_FK_MAP.get(table, set())
    uuid_fks = JOIN_UUID_FK.get(table, set())
    profil_fk_cols = {"profil_pilote_id"}

    insert_rows = []
    for row in block["rows"]:
        values = []
        for i, col in enumerate(cols):
            val = row[i]
            if col in fk_ints:
                if col in profil_fk_cols:
                    values.append(offset_int(val, PROFIL_PILOTE_ID_MAP))
                else:
                    values.append(offset_int(val))
            elif col in uuid_fks:
                values.append(map_uuid(val))
            elif col == "qualification_id":
                values.append(val if val != "\\N" else "NULL")
            else:
                values.append(escape_sql(val))
        insert_rows.append(values)

    return cols, insert_rows


def main():
    print(f"Lecture du dump : {DUMP_FILE}")
    blocks = parse_copy_blocks(str(DUMP_FILE))
    print(f"Tables trouvées : {len(blocks)}")

    out = []
    out.append("-- " + "=" * 78)
    out.append("-- MIGRATION PLANETAIR-GESTION → LOGIC'CIEL SaaS")
    out.append(f"-- Offset IDs: +{ID_OFFSET}  |  Client: {CLIENT_ID}  |  Date: 2026-04-09")
    out.append("-- " + "=" * 78)
    out.append("")
    out.append("BEGIN;")
    out.append("")

    # ---- COUCHE 1: nature/qualification SKIP ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 1: Référentiels partagés (nature, qualification) → SKIP")
    out.append("-- Les données sont identiques entre source et cible (mêmes IDs)")
    out.append("-- " + "-" * 70)
    out.append("")

    # ---- COUCHE 2: Users ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 2: Users (24 nouveaux, 1 mappé: m_seb@icloud.com)")
    out.append("-- " + "-" * 70)
    if "user" in blocks:
        cols, rows = process_user_table(blocks["user"])
        out.append(generate_insert("user", cols, rows))
        print(f"  user: {len(rows)} lignes à insérer (1 mappé/existant)")

    # ---- COUCHE 3: Référentiels client ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 3: Référentiels client (circuit, option, aeronef, airport, camera, contact, origine, combinaison)")
    out.append("-- " + "-" * 70)
    ref_tables = ["circuit", "option", "aeronef", "airport", "camera", "contact", "origine", "combinaison"]
    for table in ref_tables:
        if table in blocks:
            cols, rows = process_offset_table(table, blocks[table])
            if rows:
                out.append(f"-- {table}: {len(rows)} lignes")
                out.append(generate_insert(table, cols, rows))
                print(f"  {table}: {len(rows)} lignes")

    # ---- COUCHE 4: user_client_role ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 4: Liaison Users ↔ Client (user_client_role)")
    out.append("-- " + "-" * 70)
    all_user_ids = set()
    if "user" in blocks:
        for row in blocks["user"]["rows"]:
            email = row[blocks["user"]["columns"].index("email")]
            uid = row[blocks["user"]["columns"].index("id")]
            if email in SKIP_USER_EMAILS:
                mapped = USER_UUID_MAP.get(uid, uid)
                all_user_ids.add(mapped)
            else:
                all_user_ids.add(uid)

        ucr_values = []
        for uid in sorted(all_user_ids):
            roles_col = blocks["user"]["columns"].index("roles")
            email_col = blocks["user"]["columns"].index("email")
            id_col = blocks["user"]["columns"].index("id")

            original_uid = uid
            for row in blocks["user"]["rows"]:
                src_uid = row[id_col]
                mapped_uid = USER_UUID_MAP.get(src_uid, src_uid)
                if mapped_uid == uid:
                    roles_str = row[roles_col]
                    if "OIDC_ADMIN" in roles_str or "ROLE_ADMIN" in roles_str:
                        role = "admin"
                    else:
                        role = "pilot"
                    ucr_values.append([f"'{mapped_uid}'", f"'{role}'", str(CLIENT_ID)])
                    break

        out.append(generate_insert(
            "user_client_role",
            ["user_id", "role", "client_id"],
            ucr_values
        ))
        print(f"  user_client_role: {len(ucr_values)} lignes")

    # ---- COUCHE 5: profil_pilote + certificat/qualif/dispo ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 5: Profils pilotes et dépendances")
    out.append("-- " + "-" * 70)
    # Delete existing related data for mapped profil_pilote IDs (source data takes precedence)
    for target_pp_id in PROFIL_PILOTE_ID_MAP.values():
        out.append(f"-- Nettoyage données existantes pour profil_pilote id={target_pp_id} (sera remplacé par données Planetair)")
        out.append(f"DELETE FROM pilot_qualification WHERE profil_id = {target_pp_id};")
        out.append(f"DELETE FROM certificat_medical WHERE profil_id = {target_pp_id};")
        out.append(f"DELETE FROM disponibilite WHERE pilote_id = {target_pp_id};")
        out.append(f"DELETE FROM profil_pilote_qualification WHERE profil_pilote_id = {target_pp_id};")
        out.append("")
    # Import order: profil_pilote → media_object (sans entretien_id) → certificat_medical → pilot_qualification → disponibilite
    # media_object doit être AVANT certificat_medical/pilot_qualification car document_id FK
    if "profil_pilote" in blocks:
        cols, rows = process_offset_table("profil_pilote", blocks["profil_pilote"])
        if rows:
            out.append(f"-- profil_pilote: {len(rows)} lignes")
            out.append(generate_insert("profil_pilote", cols, rows))
            print(f"  profil_pilote: {len(rows)} lignes")

    # UPDATE existing profil_pilote for mapped users with source data
    if "profil_pilote" in blocks:
        pp_block = blocks["profil_pilote"]
        pp_cols = pp_block["columns"]
        pilote_id_idx = pp_cols.index("pilote_id")
        for row in pp_block["rows"]:
            mapped_uuid = USER_UUID_MAP.get(row[pilote_id_idx], row[pilote_id_idx])
            if mapped_uuid in SKIP_PROFIL_PILOTE_UUIDS:
                target_pp_id = PROFIL_PILOTE_ID_MAP.get(row[pp_cols.index("id")])
                if target_pp_id:
                    birth = escape_sql(row[pp_cols.index("birth_date")])
                    hours = row[pp_cols.index("total_flight_hours")]
                    avail = "'t'" if row[pp_cols.index("available_by_default")] == "t" else "'f'"
                    out.append(f"-- UPDATE profil_pilote id={target_pp_id} (m_seb) avec données Planetair")
                    out.append(f"UPDATE profil_pilote SET birth_date = {birth}, total_flight_hours = {hours}, available_by_default = {avail} WHERE id = {target_pp_id};\n")
                    print(f"  profil_pilote id={target_pp_id}: UPDATE avec données source")

    # media_object (AVANT certificat/pilot_qualification) — entretien_id mis à NULL temporairement
    media_entretien_updates = []
    if "media_object" in blocks:
        mo_block = blocks["media_object"]
        mo_cols = mo_block["columns"]
        entretien_idx = mo_cols.index("entretien_id")

        # Collect entretien_id values for later UPDATE
        for row in mo_block["rows"]:
            if row[entretien_idx] != "\\N":
                src_id = row[mo_cols.index("id")]
                new_id = offset_int(src_id)
                ent_id = offset_int(row[entretien_idx])
                media_entretien_updates.append((new_id, ent_id))
                row[entretien_idx] = "\\N"  # Nullify temporarily

        cols, rows = process_offset_table("media_object", mo_block)
        if rows:
            out.append(f"-- media_object: {len(rows)} lignes (entretien_id=NULL temporaire)")
            out.append(generate_insert("media_object", cols, rows))
            print(f"  media_object: {len(rows)} lignes (entretien_id différé)")

    for table in ["certificat_medical", "pilot_qualification", "disponibilite"]:
        if table in blocks:
            cols, rows = process_offset_table(table, blocks[table])
            if rows:
                out.append(f"-- {table}: {len(rows)} lignes")
                out.append(generate_insert(table, cols, rows))
                print(f"  {table}: {len(rows)} lignes")

    # ---- COUCHE 6: Métier principal (en bloc pour FK croisées) ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 6: Métier principal (reservation, prestation, vol, landing, carnet_vol)")
    out.append("-- Les FK croisées sont toutes décalées de +10000 en cohérence")
    out.append("-- " + "-" * 70)
    business_tables = ["cadeau", "prestation", "reservation", "vol", "landing", "carnet_vol"]
    for table in business_tables:
        if table in blocks:
            cols, rows = process_offset_table(table, blocks[table])
            if rows:
                out.append(f"-- {table}: {len(rows)} lignes")
                out.append(generate_insert(table, cols, rows))
                print(f"  {table}: {len(rows)} lignes")

    # ---- COUCHE 7: Métier secondaire ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 7: Métier secondaire (payment, payment_detail, cadeau, passager, entretien, rappel, media_object)")
    out.append("-- " + "-" * 70)
    secondary_tables = ["payment", "payment_detail", "passager", "entretien", "rappel"]
    for table in secondary_tables:
        if table in blocks and table not in SKIP_TABLES:
            cols, rows = process_offset_table(table, blocks[table])
            if rows:
                out.append(f"-- {table}: {len(rows)} lignes")
                out.append(generate_insert(table, cols, rows))
                print(f"  {table}: {len(rows)} lignes")

    # Restore media_object.entretien_id for deferred rows
    if media_entretien_updates:
        out.append("-- Restauration media_object.entretien_id (différé car entretien importé après)")
        for mo_id, ent_id in media_entretien_updates:
            out.append(f"UPDATE media_object SET entretien_id = {ent_id} WHERE id = {mo_id};")
        out.append("")
        print(f"  media_object entretien_id: {len(media_entretien_updates)} UPDATE(s)")

    # ---- COUCHE 8: Tables de jointure ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 8: Tables de jointure (7)")
    out.append("-- " + "-" * 70)
    for table in sorted(JOIN_TABLES):
        if table in blocks:
            cols, rows = process_join_table(table, blocks[table])
            if rows:
                out.append(f"-- {table}: {len(rows)} lignes")
                out.append(generate_insert(table, cols, rows))
                print(f"  {table}: {len(rows)} lignes")

    # ---- COUCHE 9: Config client ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 9: Mise à jour config client id=5")
    out.append("-- " + "-" * 70)
    if "client" in blocks and blocks["client"]["rows"]:
        row = blocks["client"]["rows"][0]
        cols = blocks["client"]["columns"]

        cam_ids_val = row[cols.index("cam_ids")]
        airport_codes_val = row[cols.index("airport_codes")]
        address_val = row[cols.index("address")]
        zipcode_val = row[cols.index("zipcode")]
        city_val = row[cols.index("city")]
        website_val = row[cols.index("website")]
        consent_val = row[cols.index("consent_text")]
        email_val = row[cols.index("email")]
        phone_val = row[cols.index("phone")]
        logo_val = row[cols.index("logo")]
        favicon_val = row[cols.index("favicon")]
        map_icon_val = row[cols.index("map_icon")]
        opacity_val = row[cols.index("opacity")]
        zoom_val = row[cols.index("zoom")]
        thanks_title_val = row[cols.index("thanks_title")]
        thanks_message_val = row[cols.index("thanks_message")]
        confirmation_message_val = row[cols.index("confirmation_message")]
        confirmation_subject_val = row[cols.index("confirmation_subject")]
        email_server_val = row[cols.index("email_server")]
        email_sender_val = row[cols.index("email_address_sender")]
        min_hours_val = row[cols.index("min_hours")]
        max_hours_val = row[cols.index("max_hours")]
        url_val = row[cols.index("url")]

        bool_cols = [
            "has_passenger_registration", "has_options", "has_partners", "has_gifts",
            "has_reservation", "has_origin_contact", "has_landing_management",
            "has_email_confirmation", "has_payment_management", "has_microtrak_tag",
            "has_webshop", "has_individual_flight_logs", "use_availability_filter",
            "has_expenses_management", "has_group_update",
        ]

        update_parts = []
        update_parts.append(f"  cam_ids = {escape_sql(cam_ids_val)}")
        update_parts.append(f"  airport_codes = {escape_sql(airport_codes_val)}")
        update_parts.append(f"  address = {escape_sql(address_val)}")
        update_parts.append(f"  zipcode = {escape_sql(zipcode_val)}")
        update_parts.append(f"  city = {escape_sql(city_val)}")
        update_parts.append(f"  website = {escape_sql(website_val)}")
        update_parts.append(f"  consent_text = {escape_sql(consent_val)}")
        update_parts.append(f"  email = {escape_sql(email_val)}")
        update_parts.append(f"  phone = {escape_sql(phone_val)}")
        update_parts.append(f"  logo = {escape_sql(logo_val)}")
        update_parts.append(f"  favicon = {escape_sql(favicon_val)}")
        update_parts.append(f"  map_icon = {escape_sql(map_icon_val)}")
        update_parts.append(f"  opacity = {opacity_val}")
        update_parts.append(f"  zoom = {zoom_val}")
        update_parts.append(f"  thanks_title = {escape_sql(thanks_title_val)}")
        update_parts.append(f"  thanks_message = {escape_sql(thanks_message_val)}")
        update_parts.append(f"  confirmation_message = {escape_sql(confirmation_message_val)}")
        update_parts.append(f"  confirmation_subject = {escape_sql(confirmation_subject_val)}")
        update_parts.append(f"  email_server = {escape_sql(email_server_val)}")
        update_parts.append(f"  email_address_sender = {escape_sql(email_sender_val)}")
        update_parts.append(f"  min_hours = {escape_sql(min_hours_val)}")
        update_parts.append(f"  max_hours = {escape_sql(max_hours_val)}")
        update_parts.append(f"  url = {escape_sql(url_val)}")
        update_parts.append(f"  seuil_medical = {row[cols.index('seuil_medical')]}")
        update_parts.append(f"  seuil_qualifications = {row[cols.index('seuil_qualifications')]}")

        for bc in bool_cols:
            if bc in cols:
                bval = row[cols.index(bc)]
                sql_bool = "true" if bval == "t" else "false"
                update_parts.append(f"  {bc} = {sql_bool}")

        out.append(f"UPDATE client SET\n" + ",\n".join(update_parts) + f"\nWHERE id = {CLIENT_ID};\n")
        print("  client config: UPDATE généré")

    # ---- COUCHE 10: Recalage séquences ----
    out.append("-- " + "-" * 70)
    out.append("-- COUCHE 10: Recalage des séquences PostgreSQL")
    out.append("-- " + "-" * 70)
    seq_tables = sorted(OFFSET_TABLES)
    for table in seq_tables:
        out.append(f"SELECT setval('{table}_id_seq', GREATEST((SELECT MAX(id) FROM \"{table}\"), (SELECT last_value FROM {table}_id_seq)));")
    out.append("SELECT setval('user_client_role_id_seq', GREATEST((SELECT MAX(id) FROM user_client_role), (SELECT last_value FROM user_client_role_id_seq)));")
    out.append("")

    out.append("COMMIT;")
    out.append("")
    out.append("-- " + "=" * 78)
    out.append("-- FIN DE LA MIGRATION")
    out.append("-- " + "=" * 78)

    sql_content = "\n".join(out)
    OUTPUT_FILE.write_text(sql_content, encoding="utf-8")
    print(f"\nSQL généré : {OUTPUT_FILE}")
    print(f"Taille : {len(sql_content)} caractères, {sql_content.count(chr(10))} lignes")


if __name__ == "__main__":
    main()
