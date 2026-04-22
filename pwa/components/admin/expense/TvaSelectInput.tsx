import TvaSelectInput from "../shared/TvaSelectInput";

interface Props {
  isCreate?: boolean;
}

const ExpenseTvaSelectInput = ({ isCreate = false }: Props) => (
  <TvaSelectInput source="tva" label="TVA appliquée" isCreate={isCreate} required />
);

export default ExpenseTvaSelectInput;
