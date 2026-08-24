import { reactive, ref } from 'vue';

export interface TableParams {
    search: string;
    pagesize: number;
    current_page: number;
    sort_column: string;
    sort_direction: string;
    column_filters: string[];
}
export class DataTable {
    params;
    cols = [];
    rows;
    total_rows;
    loading;
    table;
    search;

    constructor() {
        this.params = reactive<TableParams>({
            current_page: 1,
            pagesize: 10,
            sort_column: '',
            sort_direction: '',
            column_filters: [],
            search: '',
        });

        this.rows = ref(null);
        this.total_rows = ref(0);
        this.loading = ref(true);
        this.table = ref(null);
        this.search = ref(null);
        this.cols = [];
    }

    setCols(cols: any) {
        this.cols = cols || [];
    }

    setParams(data: TableParams) {
        this.params.current_page = data.current_page;
        this.params.pagesize = data.pagesize;
        this.params.sort_column = data.sort_column;
        this.params.sort_direction = data.sort_direction;
        this.params.column_filters = data.column_filters;
        this.params.search = data.search;
        console.log(this.params);
    }

    setSort(column: string, dir: string) {
        this.params.sort_column = column;
        this.params.sort_direction = dir;
    }
}
