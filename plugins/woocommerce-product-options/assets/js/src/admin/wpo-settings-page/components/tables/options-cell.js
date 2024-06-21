import { useGroupOptions } from '../../hooks/options';

/**
 * Displays the number of options associated to the group
 *
 * @param {Object} props
 * @param {Object} props.table
 * @return {number} optionsCount
 */
const OptionsCell = ( { table } ) => {
	const { id } = table.row.original;
	const groupOptions = useGroupOptions( id );
	const optionCount = groupOptions.isFetched ? groupOptions.data.length : 0;

	return optionCount;
};

export default OptionsCell;
