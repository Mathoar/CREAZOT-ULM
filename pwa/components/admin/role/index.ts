import { RoleList } from "./RoleList";
import { RoleEdit } from "./RoleEdit";
import { RoleShow } from "./RoleShow";

const roleResourceProps = {
  list: RoleList,
  edit: RoleEdit,
  show: RoleShow,
  options: { label: "Rôles" },
};

export default roleResourceProps;
