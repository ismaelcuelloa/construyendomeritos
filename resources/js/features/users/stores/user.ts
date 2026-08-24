import { Client } from '@/lib/client';
import { DataTable, TableParams } from '@/lib/tables';
import { ref } from 'vue';

let timer: number;
export const table = new DataTable();
export const userID = ref('');
export const userOrders = ref<any[]>([]);

table.setCols([
    { field: 'title', title: 'Nombre' },
    { field: 'enrolled_at', title: 'F. Suscripción' },
    { field: 'source', title: 'Origen' },
    { field: 'options', title: '', sort: false, width: 'fit-content' },
]);
table.setSort('subscription_date', 'asc');

export const changeServer = (data: TableParams) => {
    table.setParams(data);
    filterUsers();
};

export const handleSearch = (value: string) => {
    table.params.search = value;
};

export const goToCourse = (id: string) => {
    return Client.ADMIN_COURSES + `/${id}`;
};

export const deleteSubscription = async (id: string) => {
    try {
        await Client.delete(`${Client.ADMIN_SUBSCRIPCIONS}/${id}`);
        getCourses();
        return true;
    } catch (e) {
        console.log(e);
        return false;
    }
};

export const deleteOrder = async (orderItemId: string) => {
    try {
        // El ID viene con prefijo "order_" de orderedCourses.push()
        const itemId = orderItemId.replace('order_', '');
        await Client.delete(`${Client.ADMIN_ORDERS}/item/${itemId}`);
        
        // Actualizar userOrders eliminando el item localmente
        if (userOrders.value && userOrders.value.length > 0) {
            userOrders.value.forEach((order: any) => {
                if (order.items && order.items.length > 0) {
                    order.items = order.items.filter((item: any) => item.id.toString() !== itemId);
                }
            });
            // Filtrar órdenes vacías
            userOrders.value = userOrders.value.filter((order: any) => order.items && order.items.length > 0);
        }
        
        getCourses();
        return true;
    } catch (e) {
        console.log(e);
        return false;
    }
};

export const getCourses = async () => {
    try {
        table.loading.value = true;

        const sort: any = {};
        sort[table.params.sort_column] = table.params.sort_direction;

        const options = {
            per_page: table.params.pagesize,
            page: table.params.current_page,
            sort: JSON.stringify(sort),
            search: '',
            user_id: '',
        };

        if (table.params.search !== '') {
            options.search = table.params.search;
        }

        if (userID.value !== '') {
            options.user_id = userID.value;
        }

        const response = await Client.post(Client.ADMIN_SUBSCRIPCIONS + '/list', options);

        // Combinar subscripciones con cursos de órdenes pagadas
        const subscriptions = response.data.data || [];
        const orderedCourses: any[] = [];

        // Agregar cursos de órdenes pagadas y demos (WATI)
        if (userOrders.value && userOrders.value.length > 0) {
            userOrders.value.forEach((order: any) => {
                if (order.items && order.items.length > 0) {
                    order.items.forEach((item: any) => {
                        if (item.course) {
                            // Determinar si es demo (status_id = 7) o demo expirado (status_id = 8) o pagado (status_id = 2)
                            const isDemo = order.status_id === 7 || order.status_id === 8;
                            const isDemoExpired = order.status_id === 8;
                            const isExpired = isDemoExpired || (order.status_id === 7 && order.demo_expires_at && new Date(order.demo_expires_at) < new Date());
                            
                            // Determinar el origen basado en el número de orden
                            let source = 'Pedido';
                            if (order.number && order.number.startsWith('WATI-')) {
                                // Órdenes WATI siempre muestran "WATI"
                                source = isDemo ? (isExpired ? 'WATI DEMO (Expirado)' : 'WATI DEMO') : 'WATI';
                            } else if (order.number && order.number.startsWith('ORD-')) {
                                // Órdenes normales (ORD-) - determinar por el rol del creador
                                if (order.creator && order.creator.roles && order.creator.roles.length > 0) {
                                    const creatorRole = order.creator.roles[0].name;
                                    if (creatorRole === 'super_user') {
                                        source = 'Super U';
                                    } else if (creatorRole === 'admin') {
                                        source = 'Admin';
                                    } else {
                                        source = 'Manual';
                                    }
                                } else {
                                    source = 'Manual';
                                }
                            }
                            
                            orderedCourses.push({
                                id: `order_${item.id}`,
                                title: item.course.title,
                                course_id: item.course.id,
                                course: item.course,
                                enrolled_at: order.paid_at || order.created_at,
                                source: source,
                                is_order: true,
                                is_demo: isDemo,
                                is_expired: isExpired,
                                demo_expires_at: order.demo_expires_at,
                            });
                        }
                    });
                }
            });
        }

        // Marcar subscripciones con origen basado en el creador
        subscriptions.forEach((sub: any) => {
            // Determinar el origen basado en el rol del creador
            if (sub.creator && sub.creator.roles && sub.creator.roles.length > 0) {
                const creatorRole = sub.creator.roles[0].name;
                if (creatorRole === 'super_user') {
                    sub.source = 'Super U';
                } else if (creatorRole === 'admin') {
                    sub.source = 'Admin';
                } else {
                    sub.source = 'Suscripción';
                }
            } else {
                sub.source = 'Suscripción';
            }
            sub.is_order = false;
        });

        // Combinar resultados
        table.rows.value = [...subscriptions, ...orderedCourses];
        table.total_rows.value = response.data.total + orderedCourses.length;
    } catch {}

    table.loading.value = false;
};

export const filterUsers = () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        getCourses();
    }, 300);
};
