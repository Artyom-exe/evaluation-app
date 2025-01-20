interface Professor {
    id: number;
    name: string;
    email: string;
}

interface Year {
    id: number;
    name: string;
}

interface Module {
    id: number;
    name: string;
    description: string | null;
    professor: Professor;
    year: Year;
}

interface Form {
    id: number;
    title: string;
    statut: 'open' | 'closed';
    module: Module;
    created_at: string;
    updated_at: string;
}
