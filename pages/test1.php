<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Employés</title>
    <link rel="stylesheet" href="output.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
</head>

<body class="bg-gray-50 ">
    <div id="toast-container" class="fixed bottom-4 right-4 z-999 "></div>

    <!-- Modal Details -->
    <section id="details" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Profil de l'employé</h2>
                <button class="closeDetails text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div id="detailsEmployee" class="p-6"></div>

        </div>
    </section>

    <!-- Modal Choose Employees -->
    <section id="employesChoose" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[80vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Sélectionner un employé</h2>
                <button class="closeChoose text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div id="chooseEmployee" class="p-4 space-y-2"></div>
        </div>
    </section>

    <main class="flex gap-3 max-h-screen overflow-hidden">
        
        
        <!-- Main Content -->
        <section class="right-container lg:p-4 p-0"> 
            <div class="header p-1 my-2 text-center shadow-2xl rounded-lg  ">
                <h1 class="text-gray-700 font-bold text-2xl md:text-4xl">WorkSphere - Virtual Workspace</h1>
            </div>
            <div class="imgContainer rounded-lg shadow-lg relative flex items-center justify-center">
                <img src="plan.jpg" alt="" class="w-full h-auto block">
                <div class="gridcontainer butons-overlay w-full h-full absolute buttonContainer">
                    <div class="box1 p-4 flex flex-wrap gap-5 rounded">
                        <button class="ajout personnelAjout conferance flex justify-center text-[10px] md:text-xl h-5 w-5 md:h-9 md:w-9 rounded-full text-white font-bold border-2 border-white cursor-pointer transition-colors shadow-sm bg-blue-600 hover:bg-blue-700 hover:scale-103" data-role = "conferance">
                            +
                        </button>
                    </div>

                    <div class="box2 p-2 flex flex-wrap gap-5 bg-red-600/30 mt-2 rounded">
                        <button class="ajout serverAjout flex justify-center text-[10px] md:text-xl h-5 w-5 md:h-9 md:w-9 rounded-full text-white font-bold border-2 border-white cursor-pointer transition-colors shadow-sm bg-blue-600 hover:bg-blue-700 hover:scale-103" data-role = "conferance">
                            +
                        </button>
                    </div>

                    <div class="box3 p-2 flex flex-wrap gap-5 bg-red-600/30 mt-2 rounded">
                        <button class="ajout securiteAjout flex justify-center text-[10px] md:text-xl h-5 w-5 md:h-9 md:w-9 rounded-full text-white font-bold border-2 border-white cursor-pointer transition-colors shadow-sm bg-blue-600 hover:bg-blue-700 hover:scale-103" data-role = "conferance">
                            +
                        </button>
                    </div>

                    <div class="box4 py-1 px-3 flex flex-wrap gap-5 bg-red-600/30 ml-1 rounded">
                        <button class="ajout recepcionistAjout flex justify-center text-[10px] md:text-xl h-5 w-5 md:h-9 md:w-9 rounded-full text-white font-bold border-2 border-white cursor-pointer transition-colors shadow-sm bg-blue-600 hover:bg-blue-700 hover:scale-103" data-role = "conferance">
                            +
                        </button>
                    </div>

                    <div class="box5 py-3 px-4 flex flex-wrap gap-[2px] md:gap-3">
                        <button class="ajout personnelAjout personnel flex justify-center text-[10px] md:text-xl h-5 w-5 md:h-9 md:w-9 rounded-full text-white font-bold border-2 border-white cursor-pointer transition-colors shadow-sm bg-blue-600 hover:bg-blue-700 hover:scale-103" data-role = "conferance">
                            +
                        </button>
                    </div>

                    <div class="box6 py-3 px-4 flex flex-wrap gap-5 bg-red-600/30 rounded">
                        <button class="ajout archiveAjout flex justify-center text-[10px] md:text-xl h-5 w-5 md:h-9 md:w-9 rounded-full text-white font-bold border-2 border-white cursor-pointer transition-colors shadow-sm bg-blue-600 hover:bg-blue-700 hover:scale-103" data-role = "conferance">
                         +
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sidebar -->
        <aside class="w-80 my-4 rounded-lg bg-white border-r border-gray-200 flex flex-col max-h-screen overflow-hidden shadow-xl">
            <div class="lg:px-6 lg:py-4 md:p-0 border-b border-gray-200">
                <button class="ajoutBtn w-full text-[10px] md:text-[16px] bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-colors">
                    + Ajouter un employé
                </button>
            </div>
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 class="text-[10px] md:text-sm font-semibold text-gray-700 uppercase tracking-wide">
                    Liste des employés
                </h3>
            </div>
     
            
            <div class="employesContainer flex flex-col flex-1 px-4 py-2 overflow-y-auto overflow-x-hidden space-y-2"  style="scrollbar-width: none;"></div>
        </aside>
    </main>
    
    <!-- Form Modal -->
    <div id="formContainerAjout" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" style="scrollbar-width: none;">
            <div class="sticky top-0 bg-white border-b px-6 py-4">
                <h2 class="text-xl font-semibold text-gray-900">Ajouter un employé</h2>
            </div>

            <form id="cvForm" class="p-6 space-y-6" data-parsley-validate>
                <!-- Informations personnelles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
                        <input name="nom" type="text" placeholder="Ex: Ahmed Ahmed" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            data-parsley-pattern = "^[a-zA-Z ]+$"
                            data-parsley-trigger = "change">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rôle</label>
                        <select name="role" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Sélectionner un rôle</option>
                            <option value="Réceptionnistes">Réceptionniste</option>
                            <option value="Techniciens IT">Technicien IT</option>
                            <option value="Agents de sécurité">Agent de sécurité</option>
                            <option value="Manager">Manager</option>
                            <option value="Nettoyage">Nettoyage</option>
                            <option value="AutresRoles">Autres</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input name="email" type="email" placeholder="exemple@domaine.com" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-parsley-pattern="^[a-zA-Z0-9._]+@[a-zA-Z].+(com|ma)$"
                            data-parsley-trigger="change">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                        <input name="telephone" type="text" placeholder="+212 6 12 34 56 78" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-parsley-pattern="^\+212[6-9][0-9]{8}$"
                            data-parsley-trigger="change">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Photo (URL)</label>
                        <input type="url" name="url" id="photoUrl" placeholder="https://exemple.com/photo.jpg"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            data-parsley-pattern="^https://[^\s]+$"
                            data-parsley-trigger="change">
                        <div id="imagePreview" class="mt-3 w-20 h-20 bg-gray-100 border border-gray-300 rounded-full flex items-center justify-center overflow-hidden">
                            <span class="text-gray-400 text-xs">Aperçu</span>
                        </div>
                    </div>
                </div>

                <!-- Expériences -->
                <div class="border-t pt-6">
                    <div class="flex items-center justify-start mb-4">
                        <h2 class="text-base font-semibold text-gray-900">Expériences professionnelles</h2>
                    </div>
                    
                    <div id="experiencesList" class="space-y-3"></div>
                    <div class="flex items-center justify-center mt-4">
                        <button id="addExperienceBtn" type="button"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                            + Ajouter
                        </button>
                        </div>
                    </div>

                <!-- Templates d'Expériences -->
                <template id="experienceTemplate">
                    <div class="experiencedata border border-gray-200 rounded-lg p-4 bg-gray-50">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="text-sm font-medium text-gray-900">Nouvelle expérience</h3>
                            <button type="button" class="removeExpBtn text-red-600 hover:text-red-700 text-sm font-medium">
                                Supprimer
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Poste</label>
                                <input name="poste" type="text" placeholder="Ex: Développeur" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    data-parsley-pattern="^[A-Za-z0-9 ]+$"
                                    data-parsley-trigger="change" 
                                    >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Entreprise</label>
                                <input name="entreprise" type="text" placeholder="Ex: Tech Corp" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                                    data-parsley-pattern="^[A-Za-z ]+$"
                                    data-parsley-trigger="change">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date début</label>
                                <input name="debut" type="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date fin</label>
                                <input name="fin" type="month" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div id="finDate"></div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                <textarea name="description" rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Responsabilités et réalisations..."></textarea>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <button id="annulBtnForm" type="button"
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

 <script src="main.js"></script>
</body>
</html>