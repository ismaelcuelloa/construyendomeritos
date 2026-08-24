import axios from 'axios';

export const Client = () => {
    return this;
};

Client.HOST = '';

Client.ADMIN_USERS = '/admin/usuarios';
Client.ADMIN_ROLES = '/admin/roles';
Client.ADMIN_CATEGORIES = '/admin/categorias';
Client.ADMIN_SUBCATEGORIES = '/admin/categorias'; // subcategory routes use /admin/categorias/{categoryId}/subcategorias
Client.ADMIN_COURSES = '/admin/cursos';
Client.ADMIN_MODULES = '/admin/cursos/modulos';
Client.ADMIN_MODULES_FILES = '/admin/cursos/modulos/archivos';
Client.ADMIN_SUBSCRIPCIONS = '/admin/suscripciones';
Client.ADMIN_ORDERS = '/admin/ordenes';

Client.getHeaders = () => {
    return {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    };
};

Client.get = async (url: string, params = {}) => {
    const _params = new URLSearchParams(params);
    return axios.get(Client.getEndpoint(url + '?' + _params.toString()), {
        headers: Client.getHeaders(),
    });
};

Client.post = async (url: string, params = {}, config: any = undefined) => {
    if (typeof config === 'undefined') {
        config = {
            headers: Client.getHeaders(),
        };
    }
    return axios.post(Client.getEndpoint(url), params, config);
};

Client.put = async (url: string, params = {}) => {
    return axios.put(Client.getEndpoint(url), params, {
        headers: Client.getHeaders(),
    });
};

Client.patch = async (url: string, params = {}) => {
    return axios.patch(Client.getEndpoint(url), params, {
        headers: Client.getHeaders(),
    });
};

Client.delete = async (url: string) => {
    return axios.delete(Client.getEndpoint(url), {
        headers: Client.getHeaders(),
    });
};

Client.getEndpoint = (url: string) => {
    return Client.HOST + url;
};

Client.getUser = async () => {
    const response = await Client.get('/users/me');
    return response.data.user;
};
