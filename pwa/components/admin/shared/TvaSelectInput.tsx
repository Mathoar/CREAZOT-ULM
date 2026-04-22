import { useEffect, useRef } from "react";
import { useFormContext, useWatch } from "react-hook-form";
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
  const { setValue, register } = useFormContext();
  const currentValue = useWatch({ name: source });
  const defaultApplied = useRef(false);

  register(source);

  useEffect(() => {
    if (defaultApplied.current) return;
    if (!isCreate) return;
    if (choices.length === 0 || defaultRate === undefined) return;

    if (currentValue === undefined || currentValue === null || currentValue === "") {
      setValue(source, defaultRate, { shouldDirty: false, shouldValidate: false });
    }
    defaultApplied.current = true;
  }, [choices, defaultRate, isCreate, currentValue, setValue, source]);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const val = parseFloat(e.target.value);
    setValue(source, Number.isFinite(val) ? val : "", { shouldDirty: true, shouldValidate: true });
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
