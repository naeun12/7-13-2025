<template>
    <Loader ref="loader" />

    <div class="p-4 mt-4">
        <div class="input-group mb-4 w-100 shadow-sm rounded-pill overflow-hidden">
            <span class="input-group-text bg-white border-0">
                <i class="bi bi-search text-primary"></i>
            </span>
            <input type="text" class="form-control border-0 shadow-none" placeholder="Search Tenants name"
                aria-label="Search Tenant " v-model="searchTerm" />
        </div>
        <div class="py-3 d-flex gap-3 align-items-center">
            <!-- Dorm No Dropdown -->
            <div class="mb-2 d-flex align-items-center gap-2">
                <div class="w-100">

                    <select id="dormSelect" class="form-select form-select-lg shadow-sm" @change="filterDorms"
                        v-model="selectedDormId">
                        <option value="" disabled> Select Dorm</option>
                        <option value="all"> All dorms</option>
                        <option v-for="dorm in dorms" :key="dorm.dormID" :value="dorm.dormID">
                            {{ dorm.dormName }} (ID: {{ dorm.dormID }})
                        </option>
                    </select>


                </div>
            </div>
            <!-- Room No Dropdown -->
            <div class="mb-2 d-flex align-items-center gap-2">
                <div class="w-100">

                    <select id="dormSelect" class="form-select form-select-lg shadow-sm" v-model="selectedroomNumber"
                        @change="filterroomNumber">
                        <option value="" disabled> Select Room Number</option>
                        <option value="all">All rooms</option>
                        <option v-for="room in uniqueRooms" :key="room.fkroomID" :value="room.room?.roomNumber">
                            Room {{ room.room?.roomNumber }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="mb-2 d-flex align-items-center gap-2">
                <div class="w-100">

                    <select id="dormSelect" class="form-select form-select-lg shadow-sm"
                        @change="filterApplicationStatus" v-model="selectedapplicationStatus">
                        <option value="" disabled> Select Application Status</option>
                        <option value="all"> All Application Status</option>
                        <option value="pending"> Pending</option>
                        <option value="not approved"> Not Approved</option>
                        <option value="approved"> Approved</option>


                    </select>


                </div>
            </div>
        </div>

        <div class="table-responsive shadow-sm rounded p-3 bg-white">
            <!-- Table -->
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-primary bg-primary text-center">
                    <tr>
                        <th>Booking ID</th>
                        <th>Tenant Name</th>

                        <th>Email</th>
                        <th>Dorm Name</th>
                        <th>Room No</th>
                        <th>Move-In Date</th>
                        <th>Payment Type</th>
                        <th>Application Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    <tr v-for="booking in tenants" :key="booking.bookingID">
                        <td>{{ booking.bookingID }}</td>
                        <td>{{ booking.firstname }} {{ booking.lastname }}</td>
                        <td>{{ booking.contactEmail }}</td>
                        <td>{{ booking.room?.dorm?.dormName ?? 'N/A' }}</td>
                        <td>{{ booking.room?.roomNumber ?? 'N/A' }}</td>
                        <td>{{ booking.moveInDate }}</td>
                        <td>{{ booking.paymentType }}</td>
                        <td>
                            <span class="badge px-2 py-1" :class="{
                                'bg-success text-white': booking.status === 'Approved',
                                'bg-warning text-white': booking.status === 'pending',
                                'bg-danger text-white': booking.status === 'Not Approved',
                                'bg-secondawary text-white': !['Approved', 'Pending', 'Not Approved'].includes(booking.status)
                            }">
                                {{ booking.status }}
                            </span>
                        </td>

                        <td>
                            <button class="btn btn-sm btn-primary" @click="openTenant(booking.bookingID)">View</button>
                            <button class="btn btn-sm btn-danger ms-2"
                                @click="deleteBooking(booking.bookingID)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="lastPage > 1" class="d-flex justify-content-center align-items-center my-3">
            <nav aria-label="Page navigation">
                <ul class="pagination mb-0">
                    <li :class="['page-item', { disabled: currentPage === 1 }]">
                        <button class="page-link" :disabled="currentPage === 1"
                            @click="handlePagination(currentPage - 1)" aria-label="Previous">
                            <span aria-hidden="true">&laquo; Prev</span>
                        </button>
                    </li>

                    <li class="page-item disabled">
                        <span class="page-link">
                            Page {{ currentPage }} of {{ lastPage }}
                        </span>
                    </li>

                    <li :class="['page-item', { disabled: currentPage === lastPage }]">
                        <button class="page-link" :disabled="currentPage === lastPage"
                            @click="handlePagination(currentPage + 1)">
                            <span aria-hidden="true">Next &raquo;</span>
                        </button>
                    </li>

                </ul>
            </nav>
        </div>
        <!--Modal Tenant Appoval-->
        <!-- Use v-if to render the modal only if needed -->
        <div v-if="VisibleModalApproval" class="modal fade show d-block" style="background: rgba(0, 0, 0, 0.5);"
            tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg rounded-4 border-0">
                    <!-- Header -->
                    <div class="modal-header bg-white border-bottom-0">
                        <h5 class="modal-title text-primary fw-bold">
                            🧾 Tenant Profile
                        </h5>
                        <button type="button" class="btn-close" @click="VisibleModalApproval = false"></button>
                    </div>

                    <!-- Body -->
                    <div class="modal-body px-5">
                        <!-- Profile Picture and Status -->
                        <div class="text-center mb-4">
                            <img :src="selectedtenant.studentpictureID"
                                class="rounded-circle border border-3 border-light shadow-sm"
                                style="width: 130px; height: 130px; object-fit: cover;" />
                            <p class="mt-3">
                                <span class="badge rounded-pill px-3 py-2 fs-6" :class="{
                                    'bg-success text-white': selectedtenant.status === 'Approved',
                                    'bg-danger text-white': selectedtenant.status === 'Not Approved',
                                    'bg-warning text-dark': selectedtenant.status !== 'Approved' && selectedtenant.status !== 'Not Approved'
                                }">{{ selectedtenant.status }}</span>
                            </p>
                        </div>

                        <!-- Information Grid -->
                        <div class="row g-4">
                            <div class="col-md-6">
                                <p><strong>👤 First Name:</strong> {{ selectedtenant.firstname }}</p>
                                <p><strong>🎂 Age:</strong> {{ selectedtenant.age }}</p>
                                <p><strong>📧 Email:</strong> {{ selectedtenant.contactEmail }}</p>
                                <p><strong>📍 Address:</strong> {{ selectedtenant.room?.dorm?.address }}</p>
                                <p><strong>🚪 Room #:</strong> {{ selectedtenant.room?.roomNumber }}</p>
                                <p><strong>🚪 Move in date #:</strong> {{ selectedtenant.moveInDate }}</p>

                            </div>
                            <div class="col-md-6">
                                <p><strong>👤 Last Name:</strong> {{ selectedtenant.lastname }}</p>
                                <p><strong>🚻 Gender:</strong> {{ selectedtenant.gender }}</p>
                                <p><strong>📱 Contact:</strong> {{ selectedtenant.contactNumber }}</p>
                                <p><strong>🏠 Dorm:</strong> {{ selectedtenant.room?.dorm?.dormName || 'N/A' }}</p>
                                <p><strong>💰 Price:</strong> ₱{{
                                    Number(selectedtenant.room?.price).toLocaleString(undefined, {
                                        minimumFractionDigits: 2
                                    }) }}</p>
                                <p><strong>🚪 Move out date #:</strong> {{ selectedtenant?.moveOutDate }}</p>

                            </div>
                        </div>

                        <!-- Payment Image -->
                        <div class="text-center mt-4">
                            <p><strong>💳 Payment Type:</strong> {{ selectedtenant.paymentType }}</p>
                            <img :src="selectedtenant.paymentImage" class="img-thumbnail rounded shadow-sm mt-2"
                                style="width: 220px; height: 220px; object-fit: cover; cursor: pointer;"
                                @click="showFullImage = true" />
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="modal-footer justify-content-between bg-light border-top-0 px-4 py-3">
                        <button class="btn btn-outline-primary px-4">
                            💬 Message Tenant
                        </button>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success px-4" @click="approveTenant(selectedtenant.bookingID)"
                                :disabled="selectedtenant.status === 'Approved'">
                                ✅ Approve
                            </button>
                            <button class="btn btn-danger px-4" @click="notapproveTenant(selectedtenant.bookingID)"
                                :disabled="selectedtenant.status === 'Not Approved'">
                                ❌ Not Approve
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade show" tabindex="-1" style="display: block;" v-if="showFullImage"
        @click.self="showFullImage = false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content shadow-lg rounded-4 overflow-hidden">
                <div class="modal-body p-0 position-relative bg-dark">
                    <img :src="selectedtenant.paymentImage" class="img-fluid w-100"
                        style="object-fit: contain; max-height: 90vh;" />
                    <button type="button"
                        class="btn-close position-absolute top-0 end-0 m-3 bg-white p-2 rounded-circle"
                        aria-label="Close" @click="showFullImage = false"></button>
                </div>
                <div class="modal-footer justify-content-center bg-light border-top">
                    <button type="button" class="btn btn-outline-dark px-4" @click="showFullImage = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <Modalconfirmation ref="modal" />
    <Toastcomponents ref="toast" />



</template>
<script>
import axios from 'axios';
import Toastcomponents from '@/components/Toastcomponents.vue';
import Loader from '@/components/loader.vue';
import Modalconfirmation from '@/components/modalconfirmation.vue';
import { debounce } from 'lodash';
import { toHandlers } from 'vue';
export default {
    components: {
        Toastcomponents,
        Loader,
        Modalconfirmation
    },
    data() {
        return {
            VisibleModalApproval: false,
            tenants: [],
            filterMode: '',
            dorms: [],
            searchTerm: '',
            selectedDormId: '',
            selectedapplicationStatus: '',
            selectedroomNumber: '',
            selectedtenant: [],

            selectedBookingID: '',
            showFullImage: false,
            tenant: {},
            filteredTenants: [],
            currentPage: 1, // 👈 ADD THIS
            lastPage: 1
        };
    },

    methods: {
        async searchBooking(page = 1) {
            try {
                this.$refs.loader.loading = true;

                const response = await axios.get('/search-booking', {
                    params: {
                        search: this.searchTerm,
                        page: page
                    }
                });

                if (response.data.status === 'success') {
                    this.$refs.loader.loading = false;
                    this.selectedDormId = '';
                    this.selectedapplicationStatus = '';
                    this.selectedroomNumber = '';
                    this.tenants = response.data.booking.data;
                    this.lastPage = response.data.booking.last_page;
                } else {
                    this.$refs.toast.showToast(response.data.message, 'danger');
                }
            } catch (error) {
                console.error('Error searching bookings:', error);
                this.$refs.toast.showToast('Search failed.', 'danger');
            }
        },
        displaydorms() {
            axios.get('/api/dorms')
                .then(response => {
                    this.dorms = response.data;
                });
        },
        dormId(dorm) {
            this.selectedDorm = dorm;
        },
        filterDorms(page = 1) {
            this.$refs.loader.loading = true;

            if (this.selectedDormId === 'all') {
                this.bookingList();
                this.$refs.loader.loading = false;
                return;
            }

            axios.get(`/api/dorms/${this.selectedDormId}/tenants`, {
                params: { page }
            })
                .then(response => {
                    this.$refs.loader.loading = false;
                    this.searchTerm = '';
                    this.selectedapplicationStatus = '';
                    this.selectedroomNumber = '';

                    this.tenants = response.data.data ?? [];
                    this.lastPage = response.data.last_page ?? 1;
                })
                .catch(error => {
                    this.$refs.loader.loading = false;
                    console.error('Error fetching dorm tenants:', error);
                });
        },

        filterApplicationStatus(page = 1) {
            this.$refs.loader.loading = true;

            if (this.selectedapplicationStatus === 'all') {
                this.bookingList();
                this.$refs.loader.loading = false;
                return;
            }

            axios.get('/api/applications/booking', {
                params: {
                    selectedapplicationStatus: this.selectedapplicationStatus,
                    page: page
                }
            })
                .then(response => {
                    this.$refs.loader.loading = false;
                    this.searchTerm = '';
                    this.selectedDormId = '';
                    this.selectedroomNumber = '';
                    this.tenants = response.data.data;
                    this.lastPage = response.data.last_page;

                })
                .catch(error => {
                    this.$refs.loader.loading = false;
                    console.error("Error filtering applications:", error);
                });
        },


        filterroomNumber(page = 1) {
            this.$refs.loader.loading = true;

            if (this.selectedroomNumber === 'all') {
                this.bookingList();
                this.$refs.loader.loading = false;
                return;
            }

            axios.get(`/api/roomnumber/booking`, {
                params: {
                    roomsNumber: this.selectedroomNumber,
                    page: page,
                }
            })
                .then(response => {
                    this.$refs.loader.loading = false;
                    this.searchTerm = '';
                    this.selectedDormId = '';
                    this.selectedapplicationStatus = '';
                    if (response.data.data) {
                        this.tenants = response.data.data;
                        this.lastPage = response.data.last_page;
                    } else {
                        this.tenants = response.data;
                    }

                })
                .catch(error => {
                    this.$refs.loader.loading = false;
                    console.error("Error fetching room data:", error);
                });
        },

        async bookingList(page = 1) {
            try {
                this.$refs.loader.loading = true;

                const response = await axios.get(`/booking-list?page=${this.currentPage}`);

                if (response.data.status === 'success') {
                    this.tenants = response.data.booking.data;
                    this.lastPage = response.data.booking.last_page;
                } else {
                    this.$refs.toast.showToast(response.data.message, 'danger');
                }
            } catch (error) {
                console.error('Error fetching bookings:', error);
                this.$refs.toast.showToast("Failed to fetch bookings", 'danger');
            }
            finally {
                this.$refs.loader.loading = false;

            }
        },
        async openTenant(booking_id) {
            this.selectedBookingID = booking_id;
            this.$refs.loader.loading = true;

            try {
                const response = await axios.get(`/booking-tenant-view/${this.selectedBookingID}`);
                this.selectedtenant = response.data.tenant;
                this.VisibleModalApproval = true;
                this.$refs.loader.loading = false;
            }
            catch (error) {
                console.error('Error fetching tenant data:', error);
            }
            finally {
                this.$refs.loader.loading = false;

            }
        },
        async approveTenant(booking_id) {
            try {
                this.$refs.loader.loading = true;

                const response = await axios.post('/approve-tenant', {
                    bookingID: booking_id
                });
                // Success: show modal or success message
                this.$refs.toast.showToast(response.data.message, 'success');
                this.selectedtenant = response.data.tenant;
                this.errors = {};
                this.VisibleModalApproval = false;
                this.$refs.loader.loading = false;
                this.bookingList();

            } catch (error) {
                this.$refs.loader.loading = false;

                if (error.response && error.response.status === 403) {

                    this.$refs.toast.showToast(error.response.data.message, 'danger'); // ✅ Show danger toast
                } else {
                    console.error('Error approving tenant:', error);
                    this.$refs.toast.showToast('Something went wrong while approving the tenant.', 'danger');
                }
            } finally {
                this.$refs.loader.loading = false;
                this.VisibleModalApproval = false;

            }
        },
        async notapproveTenant(booking_id) {
            try {
                this.$refs.loader.loading = true;

                const response = await axios.post('/not-approve-tenant', {
                    bookingID: booking_id  // ✅ CamelCase
                });
                this.selectedtenant = response.data.tenant;
                this.VisibleModalApproval = false;
                this.$refs.loader.loading = false;
                this.bookingList();

            } catch (error) {
                this.$refs.loader.loading = false;
                console.error('Error approving tenant:', error.response?.data || error);
            }
            finally {
                this.$refs.loader.loading = false;

            }
        },
        async deleteBooking(booking_id) {
            try {
                // Confirm user action
                const confirmed = await this.$refs.modal.show({
                    title: 'Delete Booking',
                    message: `Confirm delete to this Book’s information?`,
                    functionName: 'Delete Booking',
                });

                if (!confirmed) {
                    return;
                }

                // Show loader
                this.$refs.loader.loading = true;

                const response = await axios.delete(`/delete-booking/${booking_id}`);

                // ✅ Show success toast
                this.$refs.toast.showToast(response.data.message, 'success');

                // Optional: Refresh list or remove from UI
                this.bookingList();

            } catch (error) {
                const errorMsg = error.response?.data?.message || 'An error occurred while deleting.';
                this.$refs.toast.showToast(errorMsg, 'danger');
                console.error('Delete error:', error);
            } finally {
                this.$refs.loader.loading = false;
            }
        },

        handlePagination(page) {

            if (page < 1 || page > this.lastPage) return;
            this.currentPage = page;


            // 👇 Priority check (only one filter is expected at a time)
            if (this.selectedapplicationStatus && this.selectedapplicationStatus !== 'all') {
                this.filterApplicationStatus(page);
            } else if (this.selectedDormId && this.selectedDormId !== 'all') {
                this.filterDorms(page);
            } else if (this.selectedroomNumber && this.selectedroomNumber !== 'all') {
                this.filterroomNumber(page);
            } else if (this.searchTerm && this.searchTerm.trim() !== '') {
                this.searchBooking(page);
            } else {
                this.bookingList(page); // Default
            }
        }


    },
    mounted() {
        this.bookingList();
        this.displaydorms();

    },
    computed:
    {
        uniqueRooms() {
            const seen = new Set();
            return this.tenants.filter(tenant => {
                if (seen.has(tenant.fkroomID)) return false;
                seen.add(tenant.fkroomID);
                return true;
            });
        }
    },

    watch: {
        searchTerm: {
            handler: debounce(function (newVal) {
                if (newVal.trim() !== '') {
                    this.searchBooking();
                } else {
                    this.bookingList();
                }
            }, 300),
            immediate: false
        },
        selectedDormId(newVal) {
            if (newVal !== '') {
                this.selectedapplicationStatus = '';
                this.selectedroomNumber = '';
                this.searchTerm = '';
                this.filterMode = 'dorm';
                this.handlePagination(1);
            }
        },
        selectedapplicationStatus(newVal) {
            if (newVal !== '') {
                this.selectedDormId = '';
                this.selectedroomNumber = '';
                this.searchTerm = '';
                this.filterMode = 'status';
                this.handlePagination(1);
            }
        },
        selectedroomNumber(newVal) {
            if (newVal !== '') {
                this.selectedDormId = '';
                this.selectedapplicationStatus = '';
                this.searchTerm = '';
                this.filterMode = 'room';
                this.handlePagination(1);
            }
        }
    }



};
</script>
