import axios from 'axios';
import { computed, onMounted, proxyRefs, reactive, ref } from 'vue';

export function useResellerPanel() {
    const notifications = ref([]);

    const walletBalance = ref(0);
    const providerBalance = ref(null);
    const profitSummary = reactive({
        orders_count: 0,
        total_sales: 0,
        total_cost: 0,
        total_profit: 0,
    });

    const catalog = ref([]);
    const catalogCategories = ref([]);
    const totalCatalogServices = ref(0);
    const catalogPagination = reactive({
        current_page: 1,
        last_page: 1,
        per_page: 50,
        total: 0,
    });
    const catalogFilters = reactive({
        category: '',
        search: '',
        tags: [],
        per_page: 50,
        page: 1,
    });

    const resellerOrders = ref([]);
    const topupRequests = ref([]);
    const selectedOrderIds = ref([]);
    const orderFilters = reactive({
        status: '',
        per_page: 10,
        page: 1,
    });
    const orderPagination = reactive({
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
    });

    const placeForm = reactive({
        catalog_service_id: '',
        quantity: 100,
        link: '',
    });

    const loadingCatalog = ref(false);
    const loadingOrders = ref(false);
    const loadingWallet = ref(false);
    const loadingProviderBalance = ref(false);
    const placingOrder = ref(false);
    const submittingTopup = ref(false);
    const actingOnOrder = ref(false);
    const checkingBulkStatus = ref(false);
    const checkingBulkRefill = ref(false);
    const checkingBulkCancel = ref(false);
    const checkingRefillStatus = ref(false);
    const refillStatusResult = ref(null);

    const refillForm = reactive({
        single_refill_id: '',
        multi_refill_ids: '',
    });

    const topupForm = reactive({
        amount: 100,
        payment_method: 'duitnow',
        reference: '',
        notes: '',
    });

    const serviceExplorer = reactive({
        platform: '',
    });

    const platformKeywords = [
        { key: 'instagram', label: 'Instagram', terms: ['instagram', 'ig', 'insta'] },
        { key: 'facebook', label: 'Facebook', terms: ['facebook', 'fb'] },
        { key: 'tiktok', label: 'TikTok', terms: ['tiktok', 'tik tok'] },
        { key: 'youtube', label: 'YouTube', terms: ['youtube', 'yt'] },
        { key: 'telegram', label: 'Telegram', terms: ['telegram'] },
        { key: 'twitter', label: 'Twitter / X', terms: ['twitter', 'x ', ' x', 'tweet'] },
        { key: 'linkedin', label: 'LinkedIn', terms: ['linkedin'] },
        { key: 'spotify', label: 'Spotify', terms: ['spotify'] },
        { key: 'website', label: 'Website', terms: ['website', 'web traffic', 'seo'] },
        { key: 'discord', label: 'Discord', terms: ['discord'] },
    ];

    const detectPlatformKey = (value) => {
        const normalized = String(value || '').toLowerCase();

        return platformKeywords.find((platform) => (
            platform.terms.some((term) => normalized.includes(term))
        ))?.key ?? null;
    };

    const formatCategoryLabel = (category) => {
        const original = String(category || '').trim();

        if (!original) {
            return 'All service types';
        }

        let label = original
            .replace(/[_-]+/g, ' ')
            .replace(/[^a-zA-Z0-9/&\s]/g, ' ')
            .replace(/\bservices?\b/gi, ' ')
            .replace(/\bmalaysia(n)?\b/gi, ' ')
            .replace(/\bworldwide\b/gi, ' ')
            .replace(/\binternational\b/gi, ' ');

        platformKeywords.forEach((platform) => {
            label = label.replace(new RegExp(`\\b${platform.label.replace(/[^\w\s/]/g, '').replace('/', '\\/') }\\b`, 'gi'), ' ');
            platform.terms.forEach((term) => {
                const safeTerm = term.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                label = label.replace(new RegExp(`\\b${safeTerm}\\b`, 'gi'), ' ');
            });
        });

        label = label.replace(/\s+/g, ' ').trim();

        if (!label) {
            return original;
        }

        return label
            .split(' ')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    };

    const platformOptions = computed(() => {
        const available = new Set(
            catalogCategories.value
                .map((category) => detectPlatformKey(category))
                .filter(Boolean),
        );

        return platformKeywords.filter((platform) => available.has(platform.key));
    });

    const serviceTagOptions = [
        { key: 'malaysian', label: 'Malaysian' },
        { key: 'refill', label: 'Refill' },
        { key: 'non_refill', label: 'Non Refill' },
    ];

    const platformCategoryOptions = computed(() => (
        catalogCategories.value
            .filter((category) => detectPlatformKey(category) === serviceExplorer.platform)
            .map((category) => ({
                value: category,
                label: formatCategoryLabel(category),
            }))
            .sort((left, right) => left.label.localeCompare(right.label))
    ));

    const selectedPlatformLabel = computed(() => (
        platformOptions.value.find((platform) => platform.key === serviceExplorer.platform)?.label ?? 'Choose platform'
    ));

    const selectedCatalog = computed(() => {
        const selectedId = Number(placeForm.catalog_service_id);
        return catalog.value.find((item) => item.id === selectedId) ?? null;
    });

    const estimatedOrderPrice = computed(() => {
        if (!selectedCatalog.value || !placeForm.quantity) {
            return 0;
        }

        const units = Number(placeForm.quantity) / 1000;
        return units * Number(selectedCatalog.value.sell_rate);
    });

    const quantityRangeLabel = computed(() => {
        if (!selectedCatalog.value) {
            return 'Select a service to view the order limit.';
        }

        return `Min ${selectedCatalog.value.min} • Max ${selectedCatalog.value.max}`;
    });

    const quantityValid = computed(() => {
        if (!selectedCatalog.value) {
            return false;
        }

        const quantity = Number(placeForm.quantity);
        return quantity >= Number(selectedCatalog.value.min) && quantity <= Number(selectedCatalog.value.max);
    });

    const canSubmitOrder = computed(() => (
        Boolean(placeForm.catalog_service_id)
        && Boolean(placeForm.link)
        && quantityValid.value
        && !placingOrder.value
    ));

    const selectedOrdersCount = computed(() => selectedOrderIds.value.length);

    const pendingOrdersCount = computed(() => resellerOrders.value.filter((order) => {
        const status = String(order.status || '').toLowerCase();
        return !['completed', 'success', 'partial', 'processing'].includes(status);
    }).length);

    const latestTopupStatus = computed(() => topupRequests.value[0]?.status ?? 'No request');

    const parseError = (error) => {
        if (error.response?.data?.errors) {
            return Object.values(error.response.data.errors).flat().join(' ');
        }

        if (error.response?.data?.message) {
            return error.response.data.message;
        }

        if (error.message) {
            return error.message;
        }

        return 'Request failed.';
    };

    const pushNotification = (type, message) => {
        const id = Date.now() + Math.random();
        notifications.value.unshift({ id, type, message });

        if (notifications.value.length > 5) {
            notifications.value = notifications.value.slice(0, 5);
        }

        setTimeout(() => {
            notifications.value = notifications.value.filter((item) => item.id !== id);
        }, 4500);
    };

    const loadWallet = async () => {
        loadingWallet.value = true;

        try {
            const { data } = await axios.get('/reseller/wallet');
            walletBalance.value = Number(data.balance ?? 0);
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            loadingWallet.value = false;
        }
    };

    const loadTopupRequests = async () => {
        try {
            const { data } = await axios.get('/reseller/topups');
            topupRequests.value = data.data ?? [];
        } catch (error) {
            pushNotification('error', parseError(error));
        }
    };

    const loadProviderBalance = async () => {
        loadingProviderBalance.value = true;

        try {
            const { data } = await axios.post('/osem/balance');
            providerBalance.value = data;
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            loadingProviderBalance.value = false;
        }
    };

    const loadCatalog = async () => {
        loadingCatalog.value = true;

        try {
            const { data } = await axios.get('/reseller/catalog', {
                params: {
                    platform: serviceExplorer.platform || undefined,
                    category: catalogFilters.category || undefined,
                    search: catalogFilters.search || undefined,
                    tags: catalogFilters.tags.length > 0 ? catalogFilters.tags : undefined,
                    per_page: catalogFilters.per_page,
                    page: catalogFilters.page,
                },
            });

            catalog.value = data.data ?? [];
            catalogPagination.current_page = data.current_page ?? 1;
            catalogPagination.last_page = data.last_page ?? 1;
            catalogPagination.per_page = data.per_page ?? 50;
            catalogPagination.total = data.total ?? 0;

            if (!placeForm.catalog_service_id && catalog.value.length > 0) {
                placeForm.catalog_service_id = String(catalog.value[0].id);
            }
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            loadingCatalog.value = false;
        }
    };

    const loadCatalogMeta = async () => {
        try {
            const { data } = await axios.get('/reseller/catalog-meta');
            catalogCategories.value = data.categories ?? [];
            totalCatalogServices.value = Number(data.total_services ?? 0);
        } catch (error) {
            pushNotification('error', parseError(error));
        }
    };

    const initializeServiceExplorer = () => {
        if (serviceExplorer.platform) {
            return;
        }

        const firstPlatform = platformOptions.value[0];

        if (!firstPlatform) {
            return;
        }

        serviceExplorer.platform = firstPlatform.key;
    };

    const loadResellerOrders = async () => {
        loadingOrders.value = true;

        try {
            const { data } = await axios.get('/reseller/orders', {
                params: {
                    status: orderFilters.status || undefined,
                    per_page: orderFilters.per_page,
                    page: orderFilters.page,
                },
            });

            resellerOrders.value = data.data ?? [];
            orderPagination.current_page = data.current_page ?? 1;
            orderPagination.last_page = data.last_page ?? 1;
            orderPagination.per_page = data.per_page ?? 10;
            orderPagination.total = data.total ?? 0;
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            loadingOrders.value = false;
        }
    };

    const loadProfitReport = async () => {
        try {
            const { data } = await axios.get('/reseller/profit-report');
            profitSummary.orders_count = data.orders_count ?? 0;
            profitSummary.total_sales = data.total_sales ?? 0;
            profitSummary.total_cost = data.total_cost ?? 0;
            profitSummary.total_profit = data.total_profit ?? 0;
        } catch (error) {
            pushNotification('error', parseError(error));
        }
    };

    const placeResellerOrder = async () => {
        placingOrder.value = true;

        try {
            const { data } = await axios.post('/reseller/orders', {
                catalog_service_id: Number(placeForm.catalog_service_id),
                quantity: Number(placeForm.quantity),
                link: placeForm.link,
            });

            pushNotification('success', data.message || 'Order placed.');
            walletBalance.value = Number(data.wallet_balance ?? walletBalance.value);
            placeForm.link = '';

            await Promise.all([loadResellerOrders(), loadProfitReport(), loadProviderBalance()]);
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            placingOrder.value = false;
        }
    };

    const submitTopupRequest = async () => {
        submittingTopup.value = true;

        try {
            const { data } = await axios.post('/reseller/topups', {
                amount: Number(topupForm.amount),
                payment_method: topupForm.payment_method,
                reference: topupForm.reference || undefined,
                notes: topupForm.notes || undefined,
            });

            pushNotification('success', data.message || 'Topup request submitted.');
            topupForm.reference = '';
            topupForm.notes = '';
            await loadTopupRequests();
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            submittingTopup.value = false;
        }
    };

    const runOrderAction = async (action, externalOrderId) => {
        actingOnOrder.value = true;

        try {
            await axios.post(`/osem/${action}`, { order: Number(externalOrderId) });
            pushNotification('success', `${action} requested for #${externalOrderId}.`);
            await loadResellerOrders();
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            actingOnOrder.value = false;
        }
    };

    const toggleOrderSelection = (externalOrderId) => {
        const id = Number(externalOrderId);

        if (selectedOrderIds.value.includes(id)) {
            selectedOrderIds.value = selectedOrderIds.value.filter((item) => item !== id);
            return;
        }

        selectedOrderIds.value.push(id);
    };

    const checkSelectedStatuses = async () => {
        if (selectedOrderIds.value.length === 0) {
            pushNotification('error', 'Select at least one order first.');
            return;
        }

        checkingBulkStatus.value = true;

        try {
            await axios.post('/osem/statuses', { orders: selectedOrderIds.value });
            pushNotification('success', 'Bulk status refresh completed.');
            await loadResellerOrders();
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            checkingBulkStatus.value = false;
        }
    };

    const refillSelectedOrders = async () => {
        if (selectedOrderIds.value.length === 0) {
            pushNotification('error', 'Select at least one order first.');
            return;
        }

        checkingBulkRefill.value = true;

        try {
            await axios.post('/osem/refills', { orders: selectedOrderIds.value });
            pushNotification('success', 'Bulk refill request submitted.');
            await loadResellerOrders();
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            checkingBulkRefill.value = false;
        }
    };

    const cancelSelectedOrders = async () => {
        if (selectedOrderIds.value.length === 0) {
            pushNotification('error', 'Select at least one order first.');
            return;
        }

        checkingBulkCancel.value = true;

        try {
            await axios.post('/osem/cancels', { orders: selectedOrderIds.value });
            pushNotification('success', 'Bulk cancel request submitted.');
            await loadResellerOrders();
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            checkingBulkCancel.value = false;
        }
    };

    const checkSingleRefillStatus = async () => {
        if (!refillForm.single_refill_id) {
            pushNotification('error', 'Enter refill ID first.');
            return;
        }

        checkingRefillStatus.value = true;

        try {
            const { data } = await axios.post('/osem/refill-status', {
                refill: Number(refillForm.single_refill_id),
            });

            refillStatusResult.value = data;
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            checkingRefillStatus.value = false;
        }
    };

    const checkMultipleRefillStatus = async () => {
        const refillIds = refillForm.multi_refill_ids
            .split(',')
            .map((id) => Number(id.trim()))
            .filter((id) => Number.isInteger(id) && id > 0);

        if (refillIds.length === 0) {
            pushNotification('error', 'Enter refill IDs in comma-separated format.');
            return;
        }

        checkingRefillStatus.value = true;

        try {
            const { data } = await axios.post('/osem/refill-statuses', {
                refills: refillIds,
            });

            refillStatusResult.value = data;
        } catch (error) {
            pushNotification('error', parseError(error));
        } finally {
            checkingRefillStatus.value = false;
        }
    };

    const formatMoney = (value) => Number(value || 0).toFixed(4);

    const selectService = (service) => {
        placeForm.catalog_service_id = String(service.id);

        if (Number(placeForm.quantity) < Number(service.min)) {
            placeForm.quantity = Number(service.min);
        }
    };

    const goCatalogPage = async (page) => {
        if (page < 1 || page > catalogPagination.last_page || page === catalogFilters.page) {
            return;
        }

        catalogFilters.page = page;
        await loadCatalog();
    };

    const goOrdersPage = async (page) => {
        if (page < 1 || page > orderPagination.last_page || page === orderFilters.page) {
            return;
        }

        orderFilters.page = page;
        await loadResellerOrders();
    };

    const toggleServiceTag = async (tagKey) => {
        if (catalogFilters.tags.includes(tagKey)) {
            catalogFilters.tags = catalogFilters.tags.filter((tag) => tag !== tagKey);
        } else {
            catalogFilters.tags = [...catalogFilters.tags, tagKey];
        }

        catalogFilters.page = 1;
        placeForm.catalog_service_id = '';
        await loadCatalog();
    };

    const selectCategory = async (category) => {
        if (catalogFilters.category === category) {
            return;
        }

        catalogFilters.category = category;
        catalogFilters.page = 1;
        placeForm.catalog_service_id = '';
        await loadCatalog();
    };

    const selectPlatform = async (platformKey) => {
        if (serviceExplorer.platform === platformKey) {
            return;
        }

        serviceExplorer.platform = platformKey;
        catalogFilters.category = '';
        catalogFilters.search = '';
        catalogFilters.tags = [];
        placeForm.catalog_service_id = '';
        catalogFilters.page = 1;

        await loadCatalog();
    };

    const orderStatusClass = (status) => {
        const normalized = String(status || 'pending').toLowerCase();

        if (['completed', 'success'].includes(normalized)) {
            return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
        }

        if (['processing', 'in progress', 'partial'].includes(normalized)) {
            return 'bg-sky-50 text-sky-700 ring-1 ring-sky-200';
        }

        if (['canceled', 'cancelled', 'rejected', 'failed'].includes(normalized)) {
            return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200';
        }

        return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
    };

    const topupStatusClass = (status) => {
        const normalized = String(status || 'pending').toLowerCase();

        if (normalized === 'approved') {
            return 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200';
        }

        if (normalized === 'rejected') {
            return 'bg-rose-50 text-rose-700 ring-1 ring-rose-200';
        }

        return 'bg-amber-50 text-amber-700 ring-1 ring-amber-200';
    };

    const refreshAll = async () => {
        await loadCatalogMeta();
        initializeServiceExplorer();

        await Promise.all([
            loadWallet(),
            loadTopupRequests(),
            loadProviderBalance(),
            loadCatalog(),
            loadResellerOrders(),
            loadProfitReport(),
        ]);
    };

    const mountPanel = () => {
        onMounted(async () => {
            await refreshAll();
        });
    };

    return proxyRefs({
        notifications,
        walletBalance,
        providerBalance,
        profitSummary,
        catalog,
        totalCatalogServices,
        catalogPagination,
        catalogFilters,
        resellerOrders,
        topupRequests,
        selectedOrderIds,
        orderFilters,
        orderPagination,
        placeForm,
        loadingCatalog,
        loadingOrders,
        loadingWallet,
        loadingProviderBalance,
        placingOrder,
        submittingTopup,
        actingOnOrder,
        checkingBulkStatus,
        checkingBulkRefill,
        checkingBulkCancel,
        checkingRefillStatus,
        refillStatusResult,
        refillForm,
        topupForm,
        serviceExplorer,
        platformOptions,
        serviceTagOptions,
        platformCategoryOptions,
        selectedPlatformLabel,
        selectedCatalog,
        estimatedOrderPrice,
        quantityRangeLabel,
        quantityValid,
        canSubmitOrder,
        selectedOrdersCount,
        pendingOrdersCount,
        latestTopupStatus,
        loadCatalog,
        loadResellerOrders,
        loadTopupRequests,
        loadProfitReport,
        placeResellerOrder,
        submitTopupRequest,
        runOrderAction,
        toggleOrderSelection,
        checkSelectedStatuses,
        refillSelectedOrders,
        cancelSelectedOrders,
        checkSingleRefillStatus,
        checkMultipleRefillStatus,
        formatMoney,
        selectService,
        selectCategory,
        toggleServiceTag,
        goCatalogPage,
        goOrdersPage,
        selectPlatform,
        orderStatusClass,
        topupStatusClass,
        refreshAll,
        mountPanel,
    });
}
