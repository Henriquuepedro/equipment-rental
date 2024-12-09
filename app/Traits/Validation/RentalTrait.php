<?php

namespace App\Traits\Validation;

use App\Http\Requests\BudgetCreatePost;
use App\Http\Requests\RentalCreatePost;
use App\Models\Budget;
use App\Models\Rental;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

trait RentalTrait
{
    private Rental|Budget|null $dataRental;
    private Collection|null $dataRentalEquipment;
    private Collection|null $dataRentalPayment;

    /**
     * @throws Exception
     */
    public function makeValidationRental(RentalCreatePost | BudgetCreatePost $request, ?int $rental_id = null, bool $is_budget = false): array
    {
        $company_id = $request->user()->company_id;
        $noCharged  = $request->input('type_rental'); // 0 = Com cobrança, 1 = Sem cobrança
        $clientId   = (int)$request->input('client');
        $zipcode    = onlyNumbers($request->input('cep'));
        $address    = filter_var($request->input('address'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $number     = filter_var($request->input('number'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $complement = filter_var($request->input('complement'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $reference  = filter_var($request->input('reference'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $neigh      = filter_var($request->input('neigh'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $city       = filter_var($request->input('city'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $state      = filter_var($request->input('state'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $lat        = filter_var($request->input('lat'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $lng        = filter_var($request->input('lng'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $use_action = !$rental_id ? 'user_insert' : 'user_update';

        if (empty($clientId) || !$this->client->getClient($clientId, $company_id)) {
            throw new Exception("Cliente não foi encontrado. Revise a aba de Cliente e Endereço.");
        }

        if ($address == '') {
            throw new Exception('Informe um endereço. Revise a aba de Cliente e Endereço.');
        }
        if ($number == '') {
            throw new Exception('Informe um número para o endereço. Revise a aba de Cliente e Endereço.');
        }
        if ($neigh == '') {
            throw new Exception('Informe um bairro. Revise a aba de Cliente e Endereço.');
        }
        if ($city == '') {
            throw new Exception('Informe uma cidade. Revise a aba de Cliente e Endereço.');
        }
        if ($state == '') {
            throw new Exception('Informe um estado. Revise a aba de Cliente e Endereço.');
        }
        if ($lat == '' || $lng == '') {
            throw new Exception('Confirme o endereço no mapa. Revise a aba de Cliente e Endereço.');
        }

        // datas da locação
        $dateDelivery           = $request->input('date_delivery') ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $request->input('date_delivery')) : null;
        $dateWithdrawal         = $request->input('date_withdrawal') ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $request->input('date_withdrawal')) : null;
        $notUseDateWithdrawal   = (bool)$request->input('not_use_date_withdrawal');

        if (!$dateDelivery) { // não reconheceu a data de entrega
            throw new Exception("Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm.");
        }

        if (!$notUseDateWithdrawal) { // usará data de retirada

            if (!$dateWithdrawal) { // não reconheceu a data de retirada
                throw new Exception("Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm.");
            }

            if ($dateDelivery->getTimestamp() >= $dateWithdrawal->getTimestamp()) { // data de entrega é maior ou igual a data de retirada
                throw new Exception("Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada.");
            }
        }

        // Equipamentos
        $responseEquipment = $this->setEquipmentRental($request, $is_budget, $rental_id);
        if (isset($responseEquipment->error)) {
            throw new Exception($responseEquipment->error);
        }
        $arrEquipment = $responseEquipment->arrEquipment;

        // Pagamento
        $arrPayment = array();
        if (!$noCharged) {
            $responsePayment = $this->setPaymentRental($request, $responseEquipment->grossValue, $is_budget, $rental_id);
            if (isset($responsePayment->error)) {
                throw new Exception($responsePayment->error);
            }

            $arrPayment = $responsePayment->arrPayment;
        } elseif ($rental_id) {
            // Quando é recebido valor em '$rental_id', consideramo que é uma atualização.
            // Se não tem pagamento, deve remover os dados de pagamentos que existem no banco.
            $this->rental_payment->remove($rental_id, $company_id);
        }

        // Resíduo
        $arrResidue = $this->setResidueRental($request, $is_budget, $rental_id);
        if (isset($arrResidue['error'])) {
            throw new Exception($arrResidue['error']);
        }

        return array(
            'arrEquipment'  => $arrEquipment,
            'arrResidue'    => $arrResidue,
            'arrPayment'    => $arrPayment,
            'rental'        => array(
                'code'                                      => $rental_id ? null : ($is_budget ? $this->budget->getNextCode($company_id) : $this->rental->getNextCode($company_id)), // get last code
                'company_id'                                => $company_id,
                'type_rental'                               => $noCharged,
                'client_id'                                 => $clientId,
                'address_zipcode'                           => $zipcode,
                'address_name'                              => $address,
                'address_number'                            => $number,
                'address_complement'                        => $complement,
                'address_reference'                         => $reference,
                'address_neigh'                             => $neigh,
                'address_city'                              => $city,
                'address_state'                             => $state,
                'address_lat'                               => $lat,
                'address_lng'                               => $lng,
                'expected_delivery_date'                    => $dateDelivery->format(DATETIME_INTERNATIONAL),
                'expected_withdrawal_date'                  => $dateWithdrawal?->format(DATETIME_INTERNATIONAL),
                'not_use_date_withdrawal'                   => $notUseDateWithdrawal,
                'gross_value'                               => !$noCharged ? $responseEquipment->grossValue : null,
                'extra_value'                               => !$noCharged ? $responsePayment->extraValue : null,
                'discount_value'                            => !$noCharged ? $responsePayment->discountValue : null,
                'net_value'                                 => !$noCharged ? $responsePayment->netValue : null,
                'calculate_net_amount_automatic'            => !$noCharged && $request->input('calculate_net_amount_automatic'),
                'automatic_parcel_distribution'             => !$noCharged && $request->input('automatic_parcel_distribution'),
                'multiply_quantity_of_equipment_per_amount' => filter_var($request->input('multiply_quantity_of_equipment_per_amount'), FILTER_VALIDATE_BOOLEAN),
                'multiply_quantity_of_equipment_per_day'    => filter_var($request->input('multiply_quantity_of_equipment_per_day'), FILTER_VALIDATE_BOOLEAN),
                'observation'                               => strip_tags($request->input('observation'), HALF_ALLOWABLE_TAGS),
                $use_action                                 => $request->user()->id
            )
        );
    }

    public function makeValidationToUpdate(RentalCreatePost | BudgetCreatePost $request, array $arrRental, array $arrEquipment, array $arrPayment): array
    {
        $company_id = $request->user()->company_id;

        return array(
            'equipment' => $this->makeValidationEquipmentToUpdate($company_id, $arrEquipment),
            'payment'   => $this->makeValidationPaymentToUpdate($company_id, $arrRental, $arrPayment)
        );
    }

    private function makeValidationEquipmentToUpdate(int $company_id, array $arrEquipment): bool
    {
        /**
         * * Equipamento
         * Ler todos os equipamentos (existente e enviados):
         * - Alterou alguma informação do equipamento, altera os dados e limpa as datas de entrega/retirada.
         * - Equipamento tem no banco e não tem na requisição, é uma remoção.
         * - Equipamento não tem no banco e tem na requisição, é uma inclusão.
         */

        // A quantidade de equipamentos no banco difere da quantidade enviada na requisição.
        if (count($this->dataRentalEquipment) != count($arrEquipment)) {
            // Limpar equipamentos.
            return false;
        }

        foreach ($arrEquipment as $equipment) {
            // Um dos pagamentos enviado na requisição, não existe no banco de dados.
            if (!$this->rental_equipment->getMatchingEquipmentToValidateLeaseUpdate($company_id, $this->dataRental->id, $equipment['equipment_id'], $equipment['quantity'], $equipment['vehicle_suggestion'], $equipment['driver_suggestion'], $equipment['use_date_diff_equip'], $equipment['expected_delivery_date'], $equipment['expected_withdrawal_date'], $equipment['not_use_date_withdrawal'], $equipment['total_value'])) {
                // Limpar equipamentos.
                return false;
            }
        }

        // Ler os pagamentos já existente no banco de dados.
        foreach ($this->dataRentalEquipment as $equipment) {
            $equipment_found = false;
            // Ler os pagamentos enviados na requisição.
            foreach ($arrEquipment as $equipment_request) {
                // Se o pagamento do banco de dados foi encontrado na requisição enviada, defino '$equipment_found' como 'true', para não excluir as pagamentos.
                if (
                    $equipment->equipment_id             == $equipment_request['equipment_id'] &&
                    $equipment->quantity                 == $equipment_request['quantity'] &&
                    $equipment->vehicle_suggestion       == $equipment_request['vehicle_suggestion'] &&
                    $equipment->driver_suggestion        == $equipment_request['driver_suggestion'] &&
                    $equipment->use_date_diff_equip      == $equipment_request['use_date_diff_equip'] &&
                    $equipment->expected_delivery_date   == $equipment_request['expected_delivery_date'] &&
                    $equipment->expected_withdrawal_date == $equipment_request['expected_withdrawal_date'] &&
                    $equipment->not_use_date_withdrawal  == $equipment_request['not_use_date_withdrawal'] &&
                    $equipment->total_value              == $equipment_request['total_value']
                ) {
                    $equipment_found = true;
                    break;
                }
            }
            if (!$equipment_found) {
                // Limpar equipamentos.
                return false;
            }
        }

        return true;
    }

    private function makeValidationPaymentToUpdate(int $company_id, array $arrRental, array $arrPayment): bool
    {
        /**
         * * Pagamento
         * Valor líquido mudou, limpa pagamentos.
         * Valor bruto mudou, limpa pagamentos.
         * Valor de desconto mudou, limpa pagamentos.
         * Valor de acréscimo mudou, limpa pagamentos.
         * Ler todos os pagamentos (existente e enviados):
         * - Alterou alguma informação do pagamento, limpa pagamentos.
         * - Pagamento tem no banco e não tem na requisição, é uma remoção.
         * - Pagamento não tem no banco e tem na requisição, é uma inclusão.
         */

        // A quantidade de pagamentos no banco de dados difere da quantidade enviada na requisição.
        if (count($this->dataRentalPayment) != count($arrPayment)) {
            // Limpar pagamentos.
            return false;
        }

        if (
            (string)$this->dataRental->net_value        != (string)$arrRental['net_value'] ||
            (string)$this->dataRental->gross_value      != (string)$arrRental['gross_value'] ||
            (string)$this->dataRental->discount_value   != (string)$arrRental['discount_value'] ||
            (string)$this->dataRental->extra_value      != (string)$arrRental['extra_value']
        ) {
            // Limpar pagamentos.
            return false;
        }

        foreach ($arrPayment as $payment) {
            // Um dos pagamentos enviado na requisição, não existe no banco de dados.
            if (!$this->rental_payment->getPaymentByRentalAndDueDateAndValue($company_id, $this->dataRental->id, $payment['due_date'], $payment['due_value'])) {
                // Limpar pagamentos.
                return false;
            }
        }

        // Ler os pagamentos já existente no banco de dados.
        foreach ($this->dataRentalPayment as $payment) {
            $payment_found = false;
            // Ler os pagamentos enviados na requisição.
            foreach ($arrPayment as $payment_request) {
                // Se o pagamento do banco de dados foi encontrado na requisição enviada, defino '$payment_found' como 'true', para não excluir as pagamentos.
                if ($payment->due_date == $payment_request['due_date'] && $payment->due_value == $payment_request['due_value']) {
                    $payment_found = true;
                    break;
                }
            }
            if (!$payment_found) {
                // Limpar pagamentos.
                return false;
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function makeValidationRentalToExchange(Request $request, int $rental_id): array
    {
        $noCharged  = $request->input('type_rental'); // 0 = Com cobrança, 1 = Sem cobrança

        // Equipamentos
        $responseEquipment = $this->setEquipmentRental($request, false, $rental_id, true);
        if (isset($responseEquipment->error)) {
            throw new Exception($responseEquipment->error);
        }
        $arrEquipment = $responseEquipment->arrEquipment;

        // Pagamento
        $arrPayment = array();
        if (!$noCharged) {
            $responsePayment = $this->setPaymentRental($request, $responseEquipment->grossValue, false, $rental_id);
            if (isset($responsePayment->error)) {
                throw new Exception($responsePayment->error);
            }

            $arrPayment = $responsePayment->arrPayment;
        }

        return array(
            'arrEquipment'  => $arrEquipment,
            'arrPayment'    => $arrPayment
        );
    }

    public function setDataRental(Rental | Budget $dataRental = null): void
    {
        $this->dataRental = $dataRental;
    }

    public function getDataRental(string $field = null)
    {
        if (is_null($field)) {
            return $this->dataRental;
        }

        return $this->dataRental->$field;
    }

    public function setDataRentalEquipment(Collection $dataRentalEquipment = null): void
    {
        $this->dataRentalEquipment = $dataRentalEquipment;
    }

    public function getDataRentalEquipment(): Collection|null
    {
        return $this->dataRentalEquipment;
    }

    public function setDataRentalPayment(Collection $dataRentalPayment = null): void
    {
        $this->dataRentalPayment = $dataRentalPayment;
    }

    public function getDataRentalPayment(): Collection|null
    {
        return $this->dataRentalPayment;
    }
}
