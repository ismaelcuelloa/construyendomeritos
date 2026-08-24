import { Content, Id, toast, ToastOptions, ToastType } from 'vue3-toastify';

class Toast {
    public static TYPE = {
        INFO: <ToastType>'info',
        SUCCESS: <ToastType>'success',
        WARNING: <ToastType>'warning',
        ERROR: <ToastType>'error',
        DEFAULT: <ToastType>'default',
        LOADING: <ToastType>'loading',
    };

    private static defaultOptions: ToastOptions = {
        autoClose: 3000,
        position: 'top-right',
    };

    private static show(content: Content, options?: ToastOptions): Id {
        return toast(content, { ...this.defaultOptions, ...options });
    }

    public static success(content: Content, options?: ToastOptions): Id {
        return this.show(content, { ...options, type: Toast.TYPE.SUCCESS });
    }

    public static error(content: Content, options?: ToastOptions): Id {
        return this.show(content, { ...options, type: Toast.TYPE.ERROR });
    }

    public static warning(content: Content, options?: ToastOptions): Id {
        return this.show(content, { ...options, type: Toast.TYPE.WARNING });
    }

    public static info(content: Content, options?: ToastOptions): Id {
        return this.show(content, { ...options, type: Toast.TYPE.INFO });
    }
    public static loading(content: Content, options?: ToastOptions): Id {
        return this.show(content, { ...options, type: Toast.TYPE.LOADING });
    }
}

export default Toast;
