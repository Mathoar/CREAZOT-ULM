import { useEffect, useRef } from "react";
import { useInput } from "react-admin";
import { useTaxRates } from "./useTaxRates";
import { TextField, MenuItem, CircularProgress } from "@mui/material";

interface TvaSelectInputProps {
  source?: string;
  label?: string;
  isCreate?: boolean;
  size?: "small" | "medium";
  fullWidth?: boolean;
  required?: boolean;
}

const TvaSelectInput = ({
  source = "tauxTva",
  label = "TVA",
  isCreate = false,
  size = "small",
  fullWidth = true,
  required = false,
}: TvaSelectInputProps) => {
  const { choices, defaultRate, isLoading } = useTaxRates();
  const {
    field: { value: currentValue, onChange: fieldOnChange },
  } = useInput({ source });
  const defaultApplied = useRef(false);

  useEffect(() => {
    if (defaultApplied.current) return;
    if (!isCreate) return;
    if (choices.length === 0 || defaultRate === undefined) return;

    if (currentValue === undefined || currentValue === null || currentValue === "") {
      fieldOnChange(defaultRate);
    }
    defaultApplied.current = true;
  }, [choices, defaultRate, isCreate, currentValue, fieldOnChange]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = parseFloat(e.target.value);
    fieldOnChange(Number.isFinite(val) ? val : "");
  };

  const displayValue = currentValue !== undefined && currentValue !== null && currentValue !== ""
    ? currentValue
    : "";

  return (
    <TextField
      select
      label={label}
      value={displayValue}
      onChange={handleChange}
      fullWidth={fullWidth}
      required={required}
      variant="filled"
      size={size}
      disabled={isLoading && choices.length === 0}
      helperText={isLoading ? "Chargement..." : choices.length === 0 ? "Aucun taux configuré" : undefined}
      sx={{ mb: 1, minWidth: 140 }}
      InputProps={{
        endAdornment: isLoading ? <CircularProgress size={18} sx={{ mr: 2 }} /> : undefined,
      }}
    >
      <MenuItem value="">—</MenuItem>
      {choices.map((c) => (
        <MenuItem key={c.id} value={c.id}>
          {c.name}
        </MenuItem>
      ))}
    </TextField>
  );
};

export default TvaSelectInput;
