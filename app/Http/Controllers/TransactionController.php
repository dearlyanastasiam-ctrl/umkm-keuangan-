<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    private function shape(Transaction $t): array
    {
        return [
            'id' => $t->id,
            'date' => $t->date->format('Y-m-d'),
            'amount' => $t->amount,
            'description' => $t->description,
            'category_id' => $t->category_id,
            'category' => $t->category->name,
            'type' => $t->category->type,
            'owner' => $t->user->name,
        ];
    }

    private function authorizeTx(Request $request, Transaction $t): void
    {
        abort_unless($request->user()->isAdmin() || $t->user_id === $request->user()->id, 403);
    }

    public function index(Request $request)
    {
        $transactions = Transaction::visibleTo($request->user())->with(['category', 'user'])
            ->when($request->from, fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($request->to, fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->when($request->type, fn ($q, $v) => $q->whereHas('category', fn ($c) => $c->where('type', $v)))
            ->when($request->category_id, fn ($q, $v) => $q->where('category_id', $v))
            ->latest('date')->latest('id')
            ->paginate(15)->withQueryString()
            ->through(fn ($t) => $this->shape($t));

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'categories' => Category::orderBy('type')->orderBy('name')->get(['id', 'name', 'type']),
            'filters' => $request->only(['from', 'to', 'type', 'category_id']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Transactions/Form', [
            'transaction' => null,
            'categories' => Category::orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $request->user()->transactions()->create($data);

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dicatat.');
    }

    public function edit(Request $request, Transaction $transaction)
    {
        $this->authorizeTx($request, $transaction);
        $transaction->load(['category', 'user']);

        return Inertia::render('Transactions/Form', [
            'transaction' => $this->shape($transaction),
            'categories' => Category::orderBy('name')->get(['id', 'name', 'type']),
        ]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $this->authorizeTx($request, $transaction);

        $transaction->update($request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000000000'],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return redirect()->route('transactions.index')->with('success', 'Transaksi diperbarui.');
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $this->authorizeTx($request, $transaction);
        $transaction->delete();

        return back()->with('success', 'Transaksi dihapus.');
    }
}
