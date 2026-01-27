import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';

export const useInventoryStore = defineStore('inventory', () => {
  
  // --- 1. DATA UNIT ---
  const defaultUnits = [
    { id: 1, alias: 'UID Jatim', name: 'PT PLN (Persero) Unit Induk Distribusi Jawa Timur', address: 'Jl. Embong Trengguli No. 19-21, Surabaya', phone: '(031) 5340651', ref_id: 'UID-001', parent_id: 0, is_active: 1, created_at: '15/01/2023' },
    { id: 2, alias: 'UID Jabar', name: 'PT PLN (Persero) Unit Induk Distribusi Jawa Barat', address: 'Jl. Asia Afrika No. 63, Bandung', phone: '(022) 4230747', ref_id: 'UID-002', parent_id: 0, is_active: 1, created_at: '20/02/2023' },
    { id: 3, alias: 'UID Jaya', name: 'PT PLN (Persero) Unit Induk Distribusi Jakarta Raya', address: 'Jl. M.I. Ridwan Rais No. 1, Jakarta Pusat', phone: '(021) 3454000', ref_id: 'UID-003', parent_id: 0, is_active: 1, created_at: '10/03/2023' },
    { id: 4, alias: 'UID Jateng', name: 'PT PLN (Persero) Unit Induk Distribusi Jawa Tengah & DIY', address: 'Jl. Teuku Umar No. 47, Semarang', phone: '(024) 8411991', ref_id: 'UID-004', parent_id: 0, is_active: 1, created_at: '05/04/2023' },
    { id: 5, alias: 'UID Bali', name: 'PT PLN (Persero) Unit Induk Distribusi Bali', address: 'Jl. Letda Tantular No. 1, Denpasar', phone: '(0361) 221960', ref_id: 'UID-005', parent_id: 0, is_active: 1, created_at: '12/05/2023' },
  ];

  // --- 2. DATA MASTER ATK ---
  const defaultATKs = [
    { id: 1, code: 'ATK-001', name: 'Pensil 2B Faber-Castell', category_id: 1, description: 'Pensil ujian standar komputer', min_stock: 10, max_stock: 100, price: 3500, uom: 'Pcs', status: 'Active', created_by: 'System', created_at: '01/01/2023', url_photo: 'contoh.jpeg' },
    { id: 2, code: 'ATK-002', name: 'Kertas A4 Sinar Dunia 80gr', category_id: 2, description: 'Kertas HVS putih ukuran A4', min_stock: 20, max_stock: 200, price: 45000, uom: 'Rim', status: 'Active', created_by: 'System', created_at: '01/01/2023', url_photo: 'contoh.jpeg' },
    { id: 3, code: 'ATK-003', name: 'Tinta Printer Epson 003 Black', category_id: 3, description: 'Tinta botol original Epson L3110', min_stock: 5, max_stock: 50, price: 85000, uom: 'Botol', status: 'Active', created_by: 'System', created_at: '02/01/2023', url_photo: 'contoh.jpeg' },
    { id: 4, code: 'ATK-004', name: 'Pulpen Standard AE7 Hitam', category_id: 1, description: 'Pulpen bola mata 0.5mm', min_stock: 50, max_stock: 500, price: 2500, uom: 'Pcs', status: 'Active', created_by: 'System', created_at: '02/01/2023', url_photo: 'contoh.jpeg' },
    { id: 5, code: 'ATK-005', name: 'Spidol Snowman Boardmarker Black', category_id: 1, description: 'Spidol papan tulis hitam', min_stock: 20, max_stock: 200, price: 8500, uom: 'Pcs', status: 'Active', created_by: 'System', created_at: '03/01/2023', url_photo: 'contoh.jpeg' },
  ];

  // --- 3. DATA STOK ---
  const defaultStocks = [
    { id: 101, item_id: 1, unit_id: 1, stock: 150, stock_min: 20, price: 3500, status: 'Active', created_at: '01/01/2023', updated_at: '10/12/2023', batches: [{ id:1, date:'2023-01-01', price:3500, stock:150 }] },
    { id: 102, item_id: 2, unit_id: 1, stock: 500, stock_min: 50, price: 45000, status: 'Active', created_at: '01/01/2023', batches: [{ id:2, date:'2023-10-05', price:45000, stock:500 }] },
    { id: 103, item_id: 3, unit_id: 2, stock: 3, stock_min: 5, price: 85000, status: 'Active', created_at: '01/01/2023', batches: [{ id:3, date:'2023-09-15', price:85000, stock:3 }] },
    { id: 104, item_id: 4, unit_id: 3, stock: 0, stock_min: 10, price: 2500, status: 'Active', created_at: '05/01/2023', batches: [] },
    { id: 105, item_id: 5, unit_id: 1, stock: 50, stock_min: 10, price: 8500, status: 'Active', created_at: '06/01/2023', batches: [{ id:4, date:'2023-01-06', price:8500, stock:50 }] },
  ];

  // --- 4. DATA PENDING APPROVALS ---
  const defaultPendingApprovals = [
    { id: 1, user: 'Andi (UID Jatim)', unit: 'UID Jatim', unit_id: 1, item_id: 2, itemName: 'Kertas A4 Sinar Dunia', itemCount: 50, value: 'Rp 2.250.000' },
    { id: 2, user: 'Budi (UID Jabar)', unit: 'UID Jabar', unit_id: 2, item_id: 3, itemName: 'Tinta Epson 003', itemCount: 10, value: 'Rp 850.000' },
  ];

  // --- 5. HISTORY ---
  const defaultHistory = [
    { id: Date.now() - 3600000, type: 'IN', date: new Date(Date.now() - 3600000).toLocaleString('id-ID'), item_id: 2, itemName: 'Kertas A4 Sinar Dunia 80gr', qty: 200, actor: 'Admin Gudang', note: 'Restock Mingguan' },
    { id: Date.now() - 7200000, type: 'OUT', date: new Date(Date.now() - 7200000).toLocaleString('id-ID'), item_id: 5, itemName: 'Spidol Snowman Boardmarker Black', qty: 50, actor: 'User Jatim', note: 'Permintaan Divisi' },
  ];

  const defaultCategories = [
    { id: 1, name: 'Alat Tulis' },
    { id: 2, name: 'Kertas & Dokumen' },
    { id: 3, name: 'Tinta & Toner' },
    { id: 4, name: 'Perlengkapan' },
    { id: 5, name: 'Lain-lain' },
  ];

  // --- STATE REAKTIF ---
  const units = ref(JSON.parse(localStorage.getItem('units')) || defaultUnits);
  const atks = ref(JSON.parse(localStorage.getItem('atks')) || defaultATKs);
  const stocks = ref(JSON.parse(localStorage.getItem('stocks')) || defaultStocks);
  const categories = ref(JSON.parse(localStorage.getItem('categories')) || defaultCategories);
  const history = ref(JSON.parse(localStorage.getItem('history')) || defaultHistory);
  const pendingApprovals = ref(JSON.parse(localStorage.getItem('pendingApprovals')) || defaultPendingApprovals);

  // --- GETTERS ---
  const lowStockItems = computed(() => {
    return stocks.value
      .filter(item => {
        const isLow = item.stock <= item.stock_min;
        const isAlreadyRequested = pendingApprovals.value.some(
          req => req.item_id === item.item_id && req.unit_id === item.unit_id
        );
        return isLow && !isAlreadyRequested;
      })
      .map(item => {
        const atk = atks.value.find(a => a.id === item.item_id) || {};
        const unit = units.value.find(u => u.id === item.unit_id) || {};
        return {
          ...item,
          name: atk.name || 'Unknown Item',
          unit: unit.alias || 'Unknown Unit'
        };
      });
  });

  // --- ACTIONS ---
  const addRestockRequest = (requestData) => {
    pendingApprovals.value.unshift(requestData);
  };

  const rejectRestockRequest = (requestId) => {
    pendingApprovals.value = pendingApprovals.value.filter(r => r.id !== requestId);
  };

  const approveRestockRequest = (requestData) => {
    const existingIndex = stocks.value.findIndex(
      s => s.item_id === requestData.item_id && s.unit_id === requestData.unit_id
    );

    const now = new Date().toLocaleString('id-ID');
    const itemMaster = atks.value.find(a => a.id === requestData.item_id);
    const itemName = itemMaster ? itemMaster.name : 'Unknown Item';

    if (existingIndex !== -1) {
      const currentStock = stocks.value[existingIndex];
      const newQty = currentStock.stock + requestData.qty;
      
      stocks.value[existingIndex] = {
        ...currentStock,
        stock: newQty,
        updated_at: new Date().toLocaleDateString('en-GB')
      };
    } else {
      const newStock = {
        id: Date.now(),
        item_id: requestData.item_id,
        unit_id: requestData.unit_id,
        stock: requestData.qty,
        stock_min: 10,
        price: requestData.price_estimate || (itemMaster ? itemMaster.price : 0),
        status: 'Active',
        created_at: new Date().toLocaleDateString('en-GB'),
        updated_at: new Date().toLocaleDateString('en-GB'),
        batches: []
      };
      stocks.value.push(newStock);
    }

    if (requestData.request_id) {
       pendingApprovals.value = pendingApprovals.value.filter(r => r.id !== requestData.request_id);
    } else {
       pendingApprovals.value = pendingApprovals.value.filter(r => !(r.item_id === requestData.item_id && r.unit_id === requestData.unit_id));
    }

    const logEntry = {
      id: Date.now(),
      type: 'IN',
      date: now,
      item_id: requestData.item_id,
      itemName: itemName,
      qty: requestData.qty,
      actor: 'Admin (Dashboard)',
      note: `Approval Request: ${requestData.user}`
    };
    history.value.unshift(logEntry);
  };

  watch(units, (val) => localStorage.setItem('units', JSON.stringify(val)), { deep: true });
  watch(atks, (val) => localStorage.setItem('atks', JSON.stringify(val)), { deep: true });
  watch(stocks, (val) => localStorage.setItem('stocks', JSON.stringify(val)), { deep: true });
  watch(history, (val) => localStorage.setItem('history', JSON.stringify(val)), { deep: true });
  watch(pendingApprovals, (val) => localStorage.setItem('pendingApprovals', JSON.stringify(val)), { deep: true });

  return { 
    units, atks, stocks, history, categories, pendingApprovals,
    lowStockItems,
    addRestockRequest, rejectRestockRequest, approveRestockRequest
  };
}); 