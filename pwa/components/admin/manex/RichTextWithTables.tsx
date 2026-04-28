"use client";

import { useState, useEffect, useCallback } from "react";
import {
  DefaultEditorOptions,
  RichTextInput,
  RichTextInputToolbar,
  LevelSelect,
  FormatButtons,
  AlignmentButtons,
  ListButtons,
  LinkButtons,
  QuoteButtons,
  ClearButtons,
  useTiptapEditor,
} from "ra-input-rich-text";
import Table from "@tiptap/extension-table";
import TableRow from "@tiptap/extension-table-row";
import TableHeader from "@tiptap/extension-table-header";
import TableCell from "@tiptap/extension-table-cell";
import { Box, Tooltip, ToggleButton, Divider } from "@mui/material";
import TableChartIcon from "@mui/icons-material/TableChart";
import AddIcon from "@mui/icons-material/Add";
import DeleteOutlineIcon from "@mui/icons-material/DeleteOutline";

const TableEditorOptions = {
  ...DefaultEditorOptions,
  extensions: [
    ...DefaultEditorOptions.extensions,
    Table.configure({ resizable: true }),
    TableRow,
    TableHeader,
    TableCell,
  ],
};

const TableButtons = () => {
  const editor = useTiptapEditor();
  const [inTable, setInTable] = useState(false);

  useEffect(() => {
    if (!editor) return;

    const onUpdate = () => {
      setInTable(editor.isActive("table"));
    };

    editor.on("selectionUpdate", onUpdate);
    editor.on("transaction", onUpdate);

    return () => {
      editor.off("selectionUpdate", onUpdate);
      editor.off("transaction", onUpdate);
    };
  }, [editor]);

  if (!editor) return null;

  const btnSx = { border: "none", px: 0.75 };

  return (
    <Box display="flex" alignItems="center" gap={0.25}>
      <Divider orientation="vertical" flexItem sx={{ mx: 0.5 }} />

      <Tooltip title="Insérer un tableau">
        <ToggleButton
          value="insertTable"
          size="small"
          onClick={() =>
            editor
              .chain()
              .focus()
              .insertTable({ rows: 3, cols: 5, withHeaderRow: true })
              .run()
          }
          sx={btnSx}
        >
          <TableChartIcon fontSize="small" />
        </ToggleButton>
      </Tooltip>

      <Tooltip title="Ajouter une ligne">
        <span>
          <ToggleButton
            value="addRow"
            size="small"
            disabled={!inTable}
            onClick={() => editor.chain().focus().addRowAfter().run()}
            sx={btnSx}
          >
            <AddIcon fontSize="small" sx={{ color: inTable ? "#38a169" : undefined }} />
            <span style={{ fontSize: 11, marginLeft: 2 }}>Ligne</span>
          </ToggleButton>
        </span>
      </Tooltip>

      <Tooltip title="Supprimer la ligne">
        <span>
          <ToggleButton
            value="deleteRow"
            size="small"
            disabled={!inTable}
            onClick={() => editor.chain().focus().deleteRow().run()}
            sx={btnSx}
          >
            <DeleteOutlineIcon fontSize="small" sx={{ color: inTable ? "#e53e3e" : undefined }} />
            <span style={{ fontSize: 11, marginLeft: 2 }}>Ligne</span>
          </ToggleButton>
        </span>
      </Tooltip>

      <Tooltip title="Ajouter une colonne">
        <span>
          <ToggleButton
            value="addCol"
            size="small"
            disabled={!inTable}
            onClick={() => editor.chain().focus().addColumnAfter().run()}
            sx={btnSx}
          >
            <AddIcon fontSize="small" sx={{ color: inTable ? "#38a169" : undefined }} />
            <span style={{ fontSize: 11, marginLeft: 2 }}>Col.</span>
          </ToggleButton>
        </span>
      </Tooltip>

      <Tooltip title="Supprimer la colonne">
        <span>
          <ToggleButton
            value="deleteCol"
            size="small"
            disabled={!inTable}
            onClick={() => editor.chain().focus().deleteColumn().run()}
            sx={btnSx}
          >
            <DeleteOutlineIcon fontSize="small" sx={{ color: inTable ? "#e53e3e" : undefined }} />
            <span style={{ fontSize: 11, marginLeft: 2 }}>Col.</span>
          </ToggleButton>
        </span>
      </Tooltip>

      <Divider orientation="vertical" flexItem sx={{ mx: 0.5 }} />

      <Tooltip title="Supprimer le tableau">
        <span>
          <ToggleButton
            value="deleteTable"
            size="small"
            disabled={!inTable}
            onClick={() => editor.chain().focus().deleteTable().run()}
            sx={{ ...btnSx, color: inTable ? "#e53e3e" : undefined }}
          >
            <DeleteOutlineIcon fontSize="small" />
            <TableChartIcon fontSize="small" />
          </ToggleButton>
        </span>
      </Tooltip>
    </Box>
  );
};

const TableToolbar = (props: any) => {
  const size = "small" as const;

  return (
    <RichTextInputToolbar {...props}>
      <LevelSelect size={size} />
      <FormatButtons size={size} />
      <AlignmentButtons size={size} />
      <ListButtons size={size} />
      <LinkButtons size={size} />
      <QuoteButtons size={size} />
      <ClearButtons size={size} />
      <TableButtons />
    </RichTextInputToolbar>
  );
};

interface RichTextWithTablesProps {
  source: string;
  label?: string | false;
  fullWidth?: boolean;
}

export const RichTextWithTables = ({
  source,
  label = false,
  fullWidth = true,
}: RichTextWithTablesProps) => {
  return (
    <>
      <style>{`
        .ProseMirror table {
          border-collapse: collapse;
          width: 100%;
          margin: 12px 0;
        }
        .ProseMirror td,
        .ProseMirror th {
          border: 1px solid #cbd5e0;
          padding: 6px 8px;
          min-width: 60px;
          vertical-align: top;
        }
        .ProseMirror th {
          background: #edf2f7;
          font-weight: bold;
          color: #1a365d;
        }
        .ProseMirror tr:nth-child(even) td {
          background: #f7fafc;
        }
        .ProseMirror .selectedCell {
          background: #e3f2fd !important;
        }
        .ProseMirror .column-resize-handle {
          position: absolute;
          right: -2px;
          top: 0;
          bottom: 0;
          width: 4px;
          background-color: #90caf9;
          pointer-events: none;
        }
        .ProseMirror table p {
          margin: 0;
        }
      `}</style>
      <RichTextInput
        source={source}
        label={label}
        fullWidth={fullWidth}
        editorOptions={TableEditorOptions}
        toolbar={<TableToolbar />}
      />
    </>
  );
};
