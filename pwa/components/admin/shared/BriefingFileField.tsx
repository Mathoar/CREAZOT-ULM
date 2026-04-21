import { useRecordContext } from "react-admin";
import { Box, Link } from "@mui/material";
import AttachFileIcon from "@mui/icons-material/AttachFile";

/**
 * Convertit un MediaObject brut (`{ "@id", contentUrl, ... }`) tel que renvoyé
 * par l'API au format attendu par <FileInput> de React-Admin (`{ src, title }`).
 *
 * Sans ce format, le FileInput considère le champ comme vide au mount et envoie
 * `null` au submit -> l'image enregistrée est effacée en BDD à chaque édition.
 *
 * Idempotent : si la valeur a déjà `rawFile` (nouvelle upload) ou `src`
 * (déjà transformée), on ne touche pas.
 */
export const formatMediaObject = (value: any) => {
    if (!value) return value;
    if (value.rawFile || value.src) return value;
    if (value["@id"] || value.contentUrl) {
        return {
            ...value,
            src: value.contentUrl,
            title: value.description || value.originalName,
        };
    }
    return value;
};

export const BriefingFileField = () => {
    const record = useRecordContext<any>();
    if (!record) return null;
    const url: string | undefined = record.contentUrl || record.src;
    const name: string =
        record.title ||
        record.description ||
        record.rawFile?.name ||
        record.originalName ||
        "Fichier";
    if (!url) {
        return (
            <Box display="inline-flex" alignItems="center" gap={0.5} fontSize="0.85rem">
                <AttachFileIcon sx={{ fontSize: 16 }} />
                {name}
            </Box>
        );
    }
    return (
        <Link href={url} target="_blank" rel="noopener noreferrer" underline="hover"
            sx={{ display: "inline-flex", alignItems: "center", gap: 0.5, fontSize: "0.85rem" }}>
            <AttachFileIcon sx={{ fontSize: 16 }} />
            {name}
        </Link>
    );
};
